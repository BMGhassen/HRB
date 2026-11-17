<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds ERP tracking fields to the articles table so we can link
     * each local article back to its iDempiere M_Product record.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Foreign key back to iDempiere's m_product_id
            $table->unsignedBigInteger('erp_product_id')->nullable()->after('id');

            // Category name pulled from iDempiere (M_Product_Category)
            $table->string('erp_category')->nullable()->after('erp_product_id');

            // Timestamp of the last successful sync for this row
            $table->timestamp('erp_synced_at')->nullable()->after('erp_category');

            // Photo / image path (may already exist on some installs — skip if so)
            if (!Schema::hasColumn('articles', 'photo')) {
                $table->string('photo')->nullable()->after('description');
            }

            // Unique index ensures no two articles map to the same ERP product
            $table->unique('erp_product_id', 'articles_erp_product_id_unique');

            // Index for fast lookups by ref (SKU) if not already indexed
            $table->index('ref', 'articles_ref_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropUnique('articles_erp_product_id_unique');
            $table->dropIndex('articles_ref_index');
            $table->dropColumn(['erp_product_id', 'erp_category', 'erp_synced_at']);
        });
    }
};
