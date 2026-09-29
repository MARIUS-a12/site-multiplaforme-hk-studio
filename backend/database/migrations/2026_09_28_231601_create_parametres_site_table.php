<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres_site', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->unique()->constrained('etablissements')->cascadeOnDelete();
            $table->boolean('accepte_commandes')->default(true);
            $table->integer('delai_preparation_minutes')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres_site');
    }
};
