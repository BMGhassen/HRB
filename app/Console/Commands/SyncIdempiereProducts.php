<?php

namespace App\Console\Commands;

use App\Services\IdempiereService;
use App\Services\IdempiereProductSync;
use Illuminate\Console\Command;

/**
 * php artisan erp:sync-products
 *
 * Syncs products from iDempiere via the REST API into the articles table.
 *
 * Examples:
 *   php artisan erp:sync-products              # sync everything
 *   php artisan erp:sync-products --dry-run    # preview without writing
 *   php artisan erp:sync-products --limit=10   # only first 10 products
 *   php artisan erp:sync-products --ping       # test REST API connection
 */
class SyncIdempiereProducts extends Command
{
    protected $signature = 'erp:sync-products
                            {--dry-run : Preview the sync without writing to the database}
                            {--limit=0 : Limit the number of products to process (0 = all)}
                            {--ping   : Test the iDempiere REST API connection and exit}';

    protected $description = 'Sync products from iDempiere ERP into the local articles table (via REST API)';

    public function handle(IdempiereService $erp): int
    {
        // ── Ping-only mode ────────────────────────────────────────────────────
        if ($this->option('ping')) {
            $this->info('Testing iDempiere REST API connection...');
            $result = $erp->ping();

            if ($result['connected']) {
                $this->info('✅ Connected to: ' . $result['base_url']);
            } else {
                $this->error('❌ Connection failed: ' . ($result['error'] ?? 'unknown error'));
                $this->line('   URL: ' . $result['base_url']);
                $this->line('   Tip: Make sure the iDempiere REST plugin is installed on your server.');
            }

            return $result['connected'] ? Command::SUCCESS : Command::FAILURE;
        }

        // ── Sync mode ─────────────────────────────────────────────────────────
        $dryRun = (bool) $this->option('dry-run');
        $limit  = (int)  $this->option('limit');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN mode — no changes will be written to the database.');
        }

        if ($limit > 0) {
            $this->line("  Limiting to first {$limit} products.");
        }

        $this->info('🔄 Starting ERP product sync (REST API)...');

        $sync   = new IdempiereProductSync($erp);
        $report = $sync->sync($dryRun, $limit);

        // ── Output table ──────────────────────────────────────────────────────
        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total ERP products fetched', $report['total']],
                ['✅ Created',                  $report['created']],
                ['✏️  Updated',                  $report['updated']],
                ['⏭️  Skipped',                  $report['skipped']],
                ['❌ Errors',                    $report['errors']],
                ['⏱  Duration (ms)',             $report['duration_ms']],
            ]
        );

        if (!empty($report['error_log'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($report['error_log'] as $err) {
                $this->line("  • {$err}");
            }
        }

        $dryLabel = $dryRun ? ' (dry run — nothing written)' : '';
        $status   = $report['errors'] > 0 ? '⚠️ Completed with errors' : '✅ Completed successfully';
        $this->newLine();
        $this->info($status . $dryLabel);

        return $report['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
