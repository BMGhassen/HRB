<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('MatF')->nullable()->change();
            $table->string('nom')->nullable()->change();
            $table->string('resp')->nullable()->change();
            $table->bigInteger('tel')->nullable()->change();
            $table->string('Adresse')->nullable()->change();
            $table->string('Region')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Not reversing nullable changes
    }
};
