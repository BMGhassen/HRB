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
            $table->string('ref')->unique();
            $table->string('designation');
            $table->integer('qte_stock');
            $table->integer('qte_instance');
            $table->integer('qte_reserve');
            $table->float('prix');
            // $table->string('photo')->nullable();
            $table->text('description');
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
