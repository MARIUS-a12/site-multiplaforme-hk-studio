<?php

namespace App\Support\Tenancy;

use App\Models\Etablissement;

class ContexteEtablissement
{
    protected ?Etablissement $etablissement = null;

    public function definir(?Etablissement $etablissement): void
    {
        $this->etablissement = $etablissement;
    }

    public function obtenir(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function id(): ?int
    {
        return $this->etablissement?->id;
    }

    public function estDefini(): bool
    {
        return $this->etablissement !== null;
    }
}
