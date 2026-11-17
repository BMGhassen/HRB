<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the state of each order's transfer to iDempiere.
     *
     * erp_status: null (draft, never sent) | pending | sent | failed
     */
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('erp_status', 20)->nullable()->after('erp_order_id')->index();
            $table->text('erp_error')->nullable()->after('erp_status');
            $table->unsignedSmallInteger('erp_attempts')->default(0)->after('erp_error');
            $table->timestamp('erp_synced_at')->nullable()->after('erp_attempts');
        });

        // Orders sent by the old synchronous flow already have an ERP ID
        DB::table('commandes')->whereNotNull('erp_order_id')->update(['erp_status' => 'sent']);
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropIndex(['erp_status']);
            $table->dropColumn(['erp_status', 'erp_error', 'erp_attempts', 'erp_synced_at']);
        });
    }
};
