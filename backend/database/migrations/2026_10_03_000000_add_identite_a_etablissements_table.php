<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 6A ter — identité de la vitrine, saisie par le commerçant (son
 * propre établissement) ou le super-admin (n'importe lequel). Toutes
 * nullables : un établissement fraîchement créé n'a encore rien renseigné,
 * et la vitrine (EtablissementVitrineResource) n'affiche que ce qui l'est.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nom');
            $table->string('telephone_whatsapp')->nullable()->after('telephone');
            $table->string('telephone_fixe')->nullable()->after('telephone_whatsapp');
            $table->string('email_contact')->nullable()->after('email');
            $table->text('adresse')->nullable()->after('email_contact');
            $table->jsonb('horaires')->nullable()->after('adresse');
            $table->string('lien_facebook')->nullable()->after('horaires');
            $table->string('lien_instagram')->nullable()->after('lien_facebook');
            $table->string('lien_tiktok')->nullable()->after('lien_instagram');
            $table->string('lien_site_web')->nullable()->after('lien_tiktok');
            $table->foreignId('logo_media_id')->nullable()->after('lien_site_web')
                ->constrained('medias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logo_media_id');
            $table->dropColumn([
                'description',
                'telephone_whatsapp',
                'telephone_fixe',
                'email_contact',
                'adresse',
                'horaires',
                'lien_facebook',
                'lien_instagram',
                'lien_tiktok',
                'lien_site_web',
            ]);
        });
    }
};
