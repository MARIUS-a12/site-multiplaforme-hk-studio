/**
 * État vide générique pour une liste : illustration + message + bouton
 * d'action désactivé (la création de produit arrive à une étape suivante,
 * ce bouton n'est qu'une préfiguration de l'écran à venir).
 */
import { PackageOpen } from 'lucide-react'

export function EtatVide() {
  return (
    <div className="flex flex-col items-center gap-3 border border-bordure bg-surface px-6 py-16 text-center">
      <PackageOpen aria-hidden="true" size={48} strokeWidth={1.5} className="text-texte-secondaire" />
      <h2 className="text-titre-section font-semibold text-texte">Aucun produit pour le moment</h2>
      <p className="max-w-sm text-corps text-texte-secondaire">
        Les produits que vous ajouterez à votre catalogue apparaîtront ici.
      </p>
      <button
        type="button"
        disabled
        className="mt-2 inline-flex h-11 cursor-not-allowed items-center rounded bg-primaire px-4 text-corps font-medium text-surface opacity-50"
      >
        Ajouter un produit
      </button>
    </div>
  )
}
