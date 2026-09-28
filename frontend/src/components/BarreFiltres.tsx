/**
 * Barre combinée recherche + filtre de statut, au-dessus de la liste de
 * produits. Le filtre est en boutons segmentés plutôt qu'un menu déroulant :
 * plus rapide au doigt, et ça montre les options sans avoir à ouvrir quoi
 * que ce soit.
 */
import { Search } from 'lucide-react'
import type { StatutProduit } from '../api/produits'
import { GroupeSegmente } from './GroupeSegmente'

const OPTIONS: { valeur: StatutProduit | ''; libelle: string }[] = [
  { valeur: '', libelle: 'Tous' },
  { valeur: 'publie', libelle: 'Publiés' },
  { valeur: 'brouillon', libelle: 'Brouillons' },
  { valeur: 'archive', libelle: 'Archivés' },
]

export function BarreFiltres({
  recherche,
  onRechercheChange,
  statut,
  onStatutChange,
}: {
  recherche: string
  onRechercheChange: (valeur: string) => void
  statut: StatutProduit | ''
  onStatutChange: (valeur: StatutProduit | '') => void
}) {
  return (
    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
      <div className="relative flex-1 sm:max-w-xs">
        <Search
          aria-hidden="true"
          size={20}
          strokeWidth={1.5}
          className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-texte-secondaire"
        />
        <input
          type="search"
          placeholder="Rechercher un produit…"
          value={recherche}
          onChange={(evenement) => onRechercheChange(evenement.target.value)}
          className="h-11 w-full rounded border border-bordure bg-surface pl-10 pr-3 text-corps text-texte transition-colors duration-150 focus:border-primaire focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1"
        />
      </div>

      <GroupeSegmente options={OPTIONS} valeur={statut} onChange={onStatutChange} />
    </div>
  )
}
