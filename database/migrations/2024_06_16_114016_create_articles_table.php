<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->nullable();
            $table->string('designation');
            $table->integer('qte_stock');
            $table->integer('qte_instance');
            $table->integer('qte_reserve');
            $table->float('prix', 8, 3);
            $table->text('description');
            $table->enum('sel',['0', '1'])->default('0');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
