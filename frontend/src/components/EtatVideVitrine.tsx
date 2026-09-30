/**
 * État vide de la grille publique : aucun produit ne correspond à la
 * recherche ou au filtre actifs (jamais "aucun produit du tout" au sens du
 * back-office — voir EtatVide, réservé à cet écran-là).
 */
import { SearchX } from 'lucide-react'

export function EtatVideVitrine() {
  return (
    <div className="flex flex-col items-center gap-3 border border-bordure bg-surface px-6 py-16 text-center">
      <SearchX aria-hidden="true" size={48} strokeWidth={1.5} className="text-texte-secondaire" />
      <h2 className="text-titre-section font-semibold text-texte">Aucun produit trouvé</h2>
      <p className="max-w-sm text-corps text-texte-secondaire">
        Essayez un autre mot-clé ou une autre catégorie.
      </p>
    </div>
  )
}
