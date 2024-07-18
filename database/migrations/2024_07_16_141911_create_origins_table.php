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
        Schema::create('origins', function (Blueprint $table) {
            $table->id();
            $table->string('ref_O');
            $table->string('OurRef');
            $table->string('marque');
            $table->unsignedBigInteger('prod_id')->nullable();
            $table->foreign('prod_id')->references('id')->on('articles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('origins', function (Blueprint $table) {
            $table->dropForeign(['prod_id']);
        });
        Schema::dropIfExists('origins');
    }
};
