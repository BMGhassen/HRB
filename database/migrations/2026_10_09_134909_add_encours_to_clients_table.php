<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('encours', 15, 3)->default(0)->after('email')->comment('Credit limit from ERP (SO_CreditLimit)');
            $table->timestamp('erp_synced_at')->nullable()->after('encours')->comment('Last time synced from iDempiere');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['encours', 'erp_synced_at']);
        });
    }
};
