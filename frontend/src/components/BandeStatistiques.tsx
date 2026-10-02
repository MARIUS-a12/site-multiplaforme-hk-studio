/**
 * Cartes statistiques au-dessus de la liste de produits (Étape 8, maquette
 * de référence) : icône dans un carré de couleur, valeur en grand, libellé,
 * une ligne de contexte. Quatre valeurs RÉELLES, calculées côté serveur
 * (voir useStatistiquesProduits et useCategories) — jamais un chiffre
 * inventé. Une carte dont la valeur ne serait pas calculable aujourd'hui
 * serait retirée plutôt qu'affichée avec un zéro trompeur ; les quatre ici
 * le sont toutes.
 */
import { AlertTriangle, FileText, FolderTree, Package } from 'lucide-react'
import type { ComponentType } from 'react'
import { useCategories } from '../hooks/useCategories'
import { useStatistiquesProduits } from '../hooks/useStatistiquesProduits'

type CouleurCarte = 'vert' | 'rouge' | 'bleu' | 'violet'

const CLASSES_CARRE: Record<CouleurCarte, string> = {
  vert: 'bg-primaire',
  rouge: 'bg-danger',
  bleu: 'bg-bleu-info',
  violet: 'bg-violet',
}

export function BandeStatistiques() {
  const { publies, brouillons, ruptures, isPending } = useStatistiquesProduits()
  const { data: categories, isPending: categoriesEnChargement } = useCategories()

  return (
    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <CarteStatistique
        icone={Package}
        couleur="vert"
        libelle="Produits publiés"
        valeur={publies}
        contexte="Visibles sur la boutique"
        chargement={isPending}
      />
      <CarteStatistique
        icone={AlertTriangle}
        couleur="rouge"
        libelle="En rupture"
        valeur={ruptures}
        contexte={ruptures > 0 ? 'À réapprovisionner' : 'Rien à signaler'}
        chargement={isPending}
      />
      <CarteStatistique
        icone={FileText}
        couleur="bleu"
        libelle="Brouillons"
        valeur={brouillons}
        contexte="En attente de publication"
        chargement={isPending}
      />
      <CarteStatistique
        icone={FolderTree}
        couleur="violet"
        libelle="Catégories"
        valeur={categories?.length ?? 0}
        contexte="Pour organiser le catalogue"
        chargement={categoriesEnChargement}
      />
    </div>
  )
}

function CarteStatistique({
  icone: Icone,
  couleur,
  libelle,
  valeur,
  contexte,
  chargement,
}: {
  icone: ComponentType<{ 'aria-hidden'?: boolean; size?: number; strokeWidth?: number; className?: string }>
  couleur: CouleurCarte
  libelle: string
  valeur: number
  contexte: string
  chargement: boolean
}) {
  return (
    <div className="rounded-lg border border-bordure bg-surface p-4 shadow-carte">
      <span
        aria-hidden="true"
        className={`flex h-10 w-10 items-center justify-center rounded-lg text-white ${CLASSES_CARRE[couleur]}`}
      >
        <Icone size={20} strokeWidth={1.75} />
      </span>
      <div className="mt-3 text-titre-page font-bold tabular-nums text-texte">{chargement ? '—' : valeur}</div>
      <div className="text-corps font-medium text-texte">{libelle}</div>
      <div className="text-petit text-texte-secondaire">{contexte}</div>
    </div>
  )
}
