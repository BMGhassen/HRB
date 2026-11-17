<?php

namespace App\Jobs;

use App\Models\Commande;
use App\Services\IdempiereProductSync;
use App\Services\IdempiereService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Sends a validated B2B order to iDempiere in the background.
 *
 * Steps: create C_Order → add C_OrderLines → complete (DocAction CO, which
 * generates the BL and Facture) → refresh local stock.
 *
 * Safe to retry: the C_Order ID is saved as soon as it exists, and on a retry
 * lines already present in iDempiere are skipped, so nothing is duplicated.
 */
class SendCommandeToErp implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Attempts before the order is marked as failed. */
    public int $tries = 5;

    /** Max seconds one attempt may run (each iDempiere call also has its own timeout). */
    public int $timeout = 180;

    /** Lock held while the job is queued or running, so one order is never sent twice in parallel. */
    public int $uniqueFor = 3600;

    public function __construct(public readonly int $commandeId) {}

    public function uniqueId(): string
    {
        return (string) $this->commandeId;
    }

    /** Wait 1 min, 5 min, 15 min, 1 h between attempts (covers a VM reboot). */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(IdempiereService $erp): void
    {
        $commande = Commande::with(['client', 'ligneCommandes.article'])->find($this->commandeId);

        if (!$commande || $commande->erp_status === 'sent') {
            return;
        }

        $commande->forceFill([
            'erp_status'   => 'pending',
            'erp_attempts' => $commande->erp_attempts + 1,
        ])->save();

        // ── Problems a retry cannot fix: fail immediately ────────────────────
        $clientCode = $commande->client?->code_client;
        if (!$clientCode) {
            $this->fail(new RuntimeException("Le client n'a pas de code ERP (code_client manquant)."));
            return;
        }

        $missing = $commande->ligneCommandes
            ->filter(fn ($l) => !$l->article || !$l->article->erp_product_id)
            ->map(fn ($l) => $l->article->ref ?? "ligne #{$l->id}")
            ->values();
        if ($missing->isNotEmpty()) {
            $this->fail(new RuntimeException('Articles sans ID ERP : ' . $missing->implode(', ')));
            return;
        }

        // ── 1. Create the order header (only once) ───────────────────────────
        if (!$commande->erp_order_id) {
            $erpOrderId = $erp->createOrder($clientCode, 'Commande B2B #' . $commande->id);
            if (!$erpOrderId) {
                throw new RuntimeException("Création de la commande ERP impossible (voir les logs [iDempiere]).");
            }
            $commande->forceFill(['erp_order_id' => $erpOrderId])->save();
        }
        $erpOrderId = (int) $commande->erp_order_id;

        // ── 2. Add lines not already in iDempiere ────────────────────────────
        $existing = $erp->getOrderLineProductIds($erpOrderId);
        $failedLines = [];
        foreach ($commande->ligneCommandes as $ligne) {
            $productId = (int) $ligne->article->erp_product_id;
            if (in_array($productId, $existing, true)) {
                continue;
            }
            if (!$erp->createOrderLine($erpOrderId, $productId, $ligne->qte, $ligne->article->prix)) {
                $failedLines[] = $ligne->article->ref ?? $productId;
            }
        }
        if ($failedLines) {
            throw new RuntimeException('Lignes non créées dans l\'ERP : ' . implode(', ', $failedLines));
        }

        // ── 3. Complete the order → BL + Facture ─────────────────────────────
        if (!$erp->completeOrder($erpOrderId)) {
            throw new RuntimeException("Complétion (DocAction CO) de la commande ERP {$erpOrderId} échouée.");
        }

        $commande->forceFill([
            'erp_status'    => 'sent',
            'erp_error'     => null,
            'erp_synced_at' => now(),
        ])->save();

        Log::info("[SendCommandeToErp] Commande {$commande->id} envoyée (C_Order {$erpOrderId}).");

        // ── 4. Refresh local stock (best effort, never fails the order) ──────
        try {
            app(IdempiereProductSync::class)->syncStockOnly();
        } catch (\Throwable $e) {
            Log::warning('[SendCommandeToErp] Post-order stock sync error: ' . $e->getMessage());
        }
    }

    /** Called after the last attempt fails, or when fail() is used. */
    public function failed(\Throwable $e): void
    {
        Log::error("[SendCommandeToErp] Commande {$this->commandeId} en échec : " . $e->getMessage());

        Commande::whereKey($this->commandeId)->update([
            'erp_status' => 'failed',
            'erp_error'  => mb_substr($e->getMessage(), 0, 2000),
        ]);
    }
}
