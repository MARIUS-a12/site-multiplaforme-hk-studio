<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boutiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->string('raison_sociale')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('statut')->default('active');
            $table->string('fuseau_horaire')->default('Africa/Abidjan');
            $table->string('devise', 3)->default('XOF');
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boutiques');
    }
};
