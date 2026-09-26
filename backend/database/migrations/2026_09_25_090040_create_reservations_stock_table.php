<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->foreignId('variante_id')->nullable()->constrained('variantes_produit')->restrictOnDelete();
            $table->integer('quantite');
            $table->string('statut')->default('active');
            $table->timestamp('expire_le')->nullable();
            $table->timestamp('liberee_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'expire_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations_stock');
    }
};
