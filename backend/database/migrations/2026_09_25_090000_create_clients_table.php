<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->string('nom');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone_whatsapp')->nullable();
            $table->json('metadonnees_json')->nullable();
            $table->timestamps();

            $table->unique(['etablissement_id', 'telephone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
