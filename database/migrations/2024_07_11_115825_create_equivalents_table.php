<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Article;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equivalents', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
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
        Schema::table('equivalents', function (Blueprint $table) {
            $table->dropForeign(['prod_id']);
        });
        Schema::dropIfExists('equivalents');
    }
};
