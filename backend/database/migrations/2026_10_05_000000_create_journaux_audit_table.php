<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace des actions sensibles (changement de mot de passe, suppression
 * d'établissement...) : qui, quand, depuis quelle adresse IP — jamais la
 * valeur de ce qui a changé. Table plate, volontairement hors de toute
 * portée tenant (un super-admin peut agir sans établissement courant).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journaux_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('details')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->timestamp('cree_le')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux_audit');
    }
};
