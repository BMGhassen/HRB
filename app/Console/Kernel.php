<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\IdempiereProductSync;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
     protected function schedule(Schedule $schedule): void
    {
        // ── 1. FAST STOCK SYNC (Every 10 minutes) ────────────────────────
        $schedule->call(function () {
            $sync = app(\App\Services\IdempiereProductSync::class);
            $report = $sync->syncStockOnly();
            \Illuminate\Support\Facades\Log::info('[Auto-Sync] Stock updated successfully.', $report);
        })
        ->everyTenMinutes()
        ->name('erp-fast-stock-sync')
        ->withoutOverlapping();

        // ── 2. DELTA CATALOG & PRICE SYNC (Hourly) ───────────────────────
        $schedule->call(function () {
            $sync = app(\App\Services\IdempiereProductSync::class);
            $report = $sync->sync(dryRun: false, limit: 0, fullSync: false);
            \Illuminate\Support\Facades\Log::info('[Auto-Sync] Hourly Delta Catalog Sync completed.', $report);
        })
        ->hourly()
        ->name('erp-hourly-delta-sync')
        ->withoutOverlapping();

        // ── 3. FULL CATALOG RECONCILIATION (Nightly at 02:00 AM) ─────────
        $schedule->call(function () {
            $sync = app(\App\Services\IdempiereProductSync::class);
            $report = $sync->sync(dryRun: false, limit: 0, fullSync: true);
            \Illuminate\Support\Facades\Log::info('[Auto-Sync] Nightly Full Catalog Sync completed.', $report);
        })
        ->dailyAt('02:00')
        ->name('erp-nightly-full-sync')
        ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
} 