import type { StatutProduit } from '../api/produits'

/**
 * Un seul code couleur par statut, partagé par BadgeStatut (le point) et
 * PageListeProduits (la barre de 3px en bord de ligne) — les deux doivent
 * toujours dire la même chose. Classes complètes et littérales (jamais
 * reconstruites par concaténation) : Tailwind ne détecte que les chaînes
 * entières présentes telles quelles dans le code source.
 */
export const CLASSE_POINT_STATUT: Record<StatutProduit, string> = {
  brouillon: 'bg-bordure',
  publie: 'bg-succes',
  archive: 'bg-texte-secondaire',
}

export const CLASSE_BORDURE_STATUT: Record<StatutProduit, string> = {
  brouillon: 'border-l-bordure',
  publie: 'border-l-succes',
  archive: 'border-l-texte-secondaire',
}
