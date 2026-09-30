<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('references_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->string('code', 8)->unique();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('variante_id')->nullable()->constrained('variantes_produit')->cascadeOnDelete();
            $table->timestamp('expire_le');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references_whatsapp');
    }
};
