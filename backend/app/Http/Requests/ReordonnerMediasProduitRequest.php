<?php

namespace App\Http\Requests;

use App\Models\Produit;
use Illuminate\Foundation\Http\FormRequest;

class ReordonnerMediasProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('produit'));
    }

    /**
     * "ordre" doit contenir EXACTEMENT les photos actuellement attachées à ce
     * produit — ni plus, ni moins, ni l'id d'une photo d'un autre produit ou
     * d'un autre établissement. Cette égalité d'ensemble est ce qui empêche
     * un commerçant d'attacher au passage le média d'un autre établissement :
     * un id étranger n'apparaît jamais dans les photos de CE produit, donc la
     * comparaison échoue et la requête est rejetée avant tout accès en base.
     */
    public function rules(): array
    {
        /** @var Produit $produit */
        $produit = $this->route('produit');
        $idsActuels = $produit->medias()->pluck('medias.id')->sort()->values()->all();

        return [
            'ordre' => [
                'required',
                'array',
                function (string $attribute, mixed $value, callable $fail) use ($idsActuels): void {
                    $fournis = collect($value)->map(fn ($id) => (int) $id)->sort()->values()->all();

                    if ($fournis !== $idsActuels) {
                        $fail('La liste doit contenir exactement les photos actuelles de ce produit, sans ajout ni omission.');
                    }
                },
            ],
            'ordre.*' => ['integer'],
        ];
    }
}
