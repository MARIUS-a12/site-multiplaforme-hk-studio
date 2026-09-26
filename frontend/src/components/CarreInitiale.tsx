import { classeFondAvatar, initiale } from '../lib/couleurAvatar'

const TAILLES = {
  40: 'h-10 w-10 text-base',
  48: 'h-12 w-12 text-lg',
} as const

/**
 * Vignette carrée avec l'initiale du nom, en attendant une vraie photo.
 * L'upload d'images n'existe pas encore (arrive avec la création de
 * produit) : ce composant est le seul endroit à changer plus tard pour
 * afficher une <img> quand une photo est disponible, sans toucher à ses
 * appelants (liste produits, en-tête).
 */
export function CarreInitiale({ nom, taille }: { nom: string; taille: 40 | 48 }) {
  return (
    <span
      aria-hidden="true"
      className={`flex shrink-0 items-center justify-center rounded font-semibold text-surface ${classeFondAvatar(nom)} ${TAILLES[taille]}`}
    >
      {initiale(nom)}
    </span>
  )
}
