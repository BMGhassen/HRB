<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\IdempiereService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncClientsFromErp extends Command
{
    protected $signature   = 'erp:sync-clients {--dry-run : Preview without saving}';
    protected $description = 'Sync customers (C_BPartner) from iDempiere into the local clients table';

    public function handle(IdempiereService $erp): int
    {
        $dryRun  = $this->option('dry-run');
        $page    = 1;
        $limit   = 100;
        $total   = 0;
        $created = 0;
        $updated = 0;

        $this->info('🔄  Starting client sync from iDempiere ERP...');
        $dryRun && $this->warn('   [DRY-RUN] No changes will be saved.');

        do {
            // ── 1. Fetch a page of BPartners ──────────────────────────────────
            try {
                $response = $erp->getModel('c_bpartner', [
                    '$filter'  => 'IsCustomer eq true and IsActive eq true',
                    '$select'  => 'C_BPartner_ID,Value,Name,TaxID,SO_CreditLimit,TotalOpenBalance',
                    '$top'     => $limit,
                    '$skip'    => ($page - 1) * $limit,
                    '$orderby' => 'Value asc',
                ]);
            } catch (\Exception $e) {
                $this->error('ERP API error: ' . $e->getMessage());
                Log::error('[ERP Sync Clients] API error: ' . $e->getMessage());
                return 1;
            }

            $records   = $response['records'] ?? [];
            $pageCount = $response['page-count'] ?? 1;

            if (empty($records)) {
                break;
            }

            // ── 2. Fetch locations for this page of BPartners ─────────────────
            $bpIds = collect($records)->pluck('id')->implode(',');

            $locations = [];
            try {
                $locResult = $erp->getModel('c_bpartner_location', [
                    '$filter'  => "IsActive eq true",
                    '$select'  => 'C_BPartner_ID,Phone,C_Location_ID,Name',
                    '$top'     => $limit * 3,
                ]);

                foreach ($locResult['records'] ?? [] as $loc) {
                    $bpId = $loc['C_BPartner_ID']['id'] ?? null;
                    if ($bpId && !isset($locations[$bpId])) {
                        $locations[$bpId] = [
                            'tel'     => $loc['Phone'] ?? null,
                            'adresse' => $loc['C_Location_ID']['Address1'] ?? null,
                            'region'  => $loc['C_Location_ID']['City'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Non-fatal — continue without location data
                Log::warning('[ERP Sync Clients] Could not fetch locations: ' . $e->getMessage());
            }

            // ── 3. Upsert each BPartner into local clients table ──────────────
            foreach ($records as $bp) {
                $code = $bp['Value'] ?? null;
                $name = $bp['Name']  ?? null;

                if (!$code) {
                    continue;
                }

                $bpId    = $bp['id'];
                $loc     = $locations[$bpId] ?? [];

                $data = [
                    'rs'           => $name,
                    'MatF'         => $bp['TaxID'] ?? null,
                    'Adresse'      => $loc['adresse'] ?? null,
                    'Region'       => $loc['region']  ?? null,
                    'tel'          => isset($loc['tel']) ? (int) preg_replace('/\D/', '', $loc['tel']) : null,
                    'encours'      => $bp['SO_CreditLimit'] ?? 0,
                    'erp_synced_at'=> now(),
                ];

                if ($dryRun) {
                    $this->line("  [DRY] {$code} | {$name} | " . ($loc['region'] ?? '—'));
                } else {
                    $exists = Client::where('code', $code)->exists();
                    Client::updateOrCreate(['code' => $code], $data);
                    $exists ? $updated++ : $created++;
                }

                $total++;
            }

            $this->info("  Page {$page}/{$pageCount} — {$total} BPartners processed so far...");
            $page++;

        } while ($page <= $pageCount);

        if (!$dryRun) {
            $this->info("✅  Sync complete: {$created} created, {$updated} updated. ({$total} total)");
            Log::info("[ERP Sync Clients] Finished: created={$created}, updated={$updated}, total={$total}");
        } else {
            $this->info("✅  Dry-run complete. {$total} BPartners found. No DB changes made.");
        }

        return 0;
    }
}
