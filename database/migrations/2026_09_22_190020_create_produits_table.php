<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('nom');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->integer('prix');
            $table->integer('prix_barre')->nullable();
            $table->string('mode_stock')->default('compte');
            $table->integer('quantite_stock')->default(0);
            $table->integer('quantite_reservee')->default(0);
            $table->boolean('disponible')->default(true);
            $table->string('statut')->default('brouillon');
            $table->timestamp('publie_le')->nullable();
            $table->timestamps();

            $table->unique(['etablissement_id', 'slug']);
            $table->index(['etablissement_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
