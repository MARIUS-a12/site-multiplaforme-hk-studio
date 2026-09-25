<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('numero');
            $table->string('statut')->default('attente_paiement');
            $table->string('source');
            $table->string('canal');
            $table->foreignId('zone_livraison_id')->nullable()->constrained('zones_livraison')->nullOnDelete();
            $table->integer('sous_total')->default(0);
            $table->integer('remise')->default(0);
            $table->integer('frais_livraison')->default(0);
            $table->integer('total')->default(0);
            $table->string('cle_idempotence')->unique();
            $table->timestamp('expire_le')->nullable();
            $table->timestamp('payee_le')->nullable();
            $table->timestamps();

            $table->unique(['etablissement_id', 'numero']);
            $table->index(['etablissement_id', 'statut', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
