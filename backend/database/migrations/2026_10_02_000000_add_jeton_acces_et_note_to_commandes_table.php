<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * jeton_acces : le numéro de commande seul se devine (CMD-000847) — voir
 * Commande::booted(), qui le génère à la création. note : message libre du
 * client au commerçant (livraison, allergie...), jamais lu par CreerCommande
 * lui-même (voir Étape 6B — ce service n'est pas réécrit), posé après coup
 * par VitrineCommandeController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('jeton_acces', 64)->nullable()->after('cle_idempotence');
            $table->text('note')->nullable()->after('frais_livraison');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['jeton_acces', 'note']);
        });
    }
};
