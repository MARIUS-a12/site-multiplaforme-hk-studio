/**
 * État vide générique pour une liste de produits : illustration + message +
 * bouton d'action, qui mène directement au formulaire de création — absent
 * pour un rôle qui n'a pas gerer_catalogue (un opérateur consulte, il ne
 * crée pas).
 */
import { PackageOpen } from 'lucide-react'
import { Link } from 'react-router-dom'

export function EtatVide({ peutCreer }: { peutCreer: boolean }) {
  return (
    <div className="flex flex-col items-center gap-3 border border-bordure bg-surface px-6 py-16 text-center">
      <PackageOpen aria-hidden="true" size={48} strokeWidth={1.5} className="text-texte-secondaire" />
      <h2 className="text-titre-section font-semibold text-texte">Aucun produit pour le moment</h2>
      <p className="max-w-sm text-corps text-texte-secondaire">
        {peutCreer
          ? 'Les produits que vous ajouterez à votre catalogue apparaîtront ici.'
          : "Les produits ajoutés par un administrateur apparaîtront ici."}
      </p>
      {peutCreer && (
        <Link
          to="/produits/nouveau"
          className="mt-2 inline-flex h-11 cursor-pointer items-center rounded bg-primaire px-4 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
        >
          Ajouter un produit
        </Link>
      )}
    </div>
  )
}
