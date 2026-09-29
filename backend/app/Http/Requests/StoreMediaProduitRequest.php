<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('produit'));
    }

    public function rules(): array
    {
        return [
            // "image" + "mimes" s'appuient sur le contenu réel du fichier
            // (sniff du type MIME), pas sur l'extension fournie par le
            // client : un fichier texte renommé en .jpg est rejeté ici.
            'photo' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.intdiv((int) config('medias.poids_max_octets'), 1024),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Choisissez une photo à envoyer.',
            'photo.image' => "Le fichier envoyé n'est pas une image valide.",
            'photo.mimes' => "Le fichier envoyé n'est pas une image valide.",
            'photo.max' => 'La photo dépasse la taille maximale autorisée (8 Mo).',
        ];
    }
}
