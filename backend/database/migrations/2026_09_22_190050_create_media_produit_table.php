<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_produit', function (Blueprint $table) {
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('medias')->cascadeOnDelete();
            $table->integer('ordre')->default(0);
            $table->boolean('est_principal')->default(false);

            $table->primary(['produit_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_produit');
    }
};
