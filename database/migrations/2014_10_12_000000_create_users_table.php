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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role',['client', 'admin', 'vendeur'])->default('client');
            $table->enum('state',['Actif', 'NonActif'])->default('NonActif');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('code_client')->nullable();
            $table->unsignedBigInteger('vendeur_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            // $table->foreign('client_id')->references('code')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
