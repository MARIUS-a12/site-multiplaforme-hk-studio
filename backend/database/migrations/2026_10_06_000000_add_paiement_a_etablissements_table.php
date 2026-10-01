<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 6C-1 — chaque établissement a SON propre compte CinetPay (la
 * plateforme ne touche jamais aux fonds d'autrui). Les trois identifiants
 * sont en "text" malgré leur contenu parfois court (site_id) : chiffrés par
 * le cast "encrypted" natif de Laravel (voir Etablissement), leur forme en
 * base est un bloc chiffré bien plus long que la valeur d'origine — un
 * varchar(255) le tronquerait silencieusement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->text('cinetpay_site_id')->nullable();
            $table->text('cinetpay_cle_api')->nullable();
            $table->text('cinetpay_secret')->nullable();
            $table->timestamp('paiement_configure_le')->nullable();
            $table->foreignId('paiement_configure_par')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paiement_configure_par');
            $table->dropColumn(['cinetpay_site_id', 'cinetpay_cle_api', 'cinetpay_secret', 'paiement_configure_le']);
        });
    }
};
