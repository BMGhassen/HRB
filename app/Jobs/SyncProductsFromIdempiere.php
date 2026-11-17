<?php

namespace App\Jobs;

use App\Services\IdempiereService;
use App\Services\IdempiereProductSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SyncProductsFromIdempiere
 *
 * Dispatchable, queueable job that pulls the full product catalog
 * from iDempiere (via REST API) and upserts it into the local articles table.
 *
 * Usage:
 *   SyncProductsFromIdempiere::dispatch();          // async via queue
 *   SyncProductsFromIdempiere::dispatchSync();      // synchronous (for testing)
 */
class SyncProductsFromIdempiere implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of attempts before the job is marked as failed */
    public int $tries = 3;

    /** Delay between retries in seconds */
    public int $backoff = 60;

    /** Maximum seconds the job may run before timing out */
    public int $timeout = 300;

    public function __construct(
        public readonly bool $dryRun = false,
        public readonly int  $limit  = 0
    ) {}

    /**
     * Execute the sync job.
     */
    public function handle(IdempiereService $erp): void
    {
        Log::info('[SyncProductsJob] Job started.');

        // Store a "sync in progress" flag so the dashboard can show it
        Cache::put('erp_sync_running', true, 600);

        try {
            $sync   = new IdempiereProductSync($erp);
            $report = $sync->sync($this->dryRun, $this->limit);

            // Persist the last sync report for the admin dashboard
            Cache::put('erp_last_sync_report', $report, now()->addDay());

            Log::info('[SyncProductsJob] Job completed.', $report);
        } finally {
            Cache::forget('erp_sync_running');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $e): void
    {
        Cache::forget('erp_sync_running');
        Log::error('[SyncProductsJob] Job failed: ' . $e->getMessage());

        // Store the failure so the admin dashboard can surface it
        Cache::put('erp_last_sync_report', [
            'total'       => 0,
            'created'     => 0,
            'updated'     => 0,
            'skipped'     => 0,
            'errors'      => 1,
            'dry_run'     => $this->dryRun,
            'synced_at'   => now()->toDateTimeString(),
            'duration_ms' => 0,
            'error_log'   => [$e->getMessage()],
        ], now()->addDay());
    }
}
