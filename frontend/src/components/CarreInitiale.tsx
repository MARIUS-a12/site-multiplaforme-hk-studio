import type { VarianteMedia } from '../api/medias'
import { classeFondAvatar, initiale } from '../lib/couleurAvatar'

// text-corps / text-titre-section : toujours l'une des quatre tailles de
// l'échelle typographique du projet (voir index.css), jamais une taille
// Tailwind par défaut (text-base, text-lg...) qui n'en fait pas partie.
const TAILLES = {
  40: 'h-10 w-10 text-corps',
  48: 'h-12 w-12 text-titre-section',
} as const

/**
 * Vignette carrée : la photo principale du produit quand il en a une,
 * sinon l'initiale de son nom en repli. C'est le seul endroit à changer
 * pour ça — liste produits et en-tête n'ont rien à savoir de la présence ou
 * non d'une photo. "arrondi" produit un cercle plutôt qu'un carré aux coins
 * arrondis — l'avatar utilisateur de la barre supérieure (Étape 8), jamais
 * les vignettes produit elles-mêmes.
 */
export function CarreInitiale({
  nom,
  taille,
  photo = null,
  arrondi = false,
}: {
  nom: string
  taille: 40 | 48
  photo?: VarianteMedia | null
  arrondi?: boolean
}) {
  const classeForme = arrondi ? 'rounded-full' : 'rounded'

  if (photo && (photo.webp || photo.jpg)) {
    return (
      <picture className={`block shrink-0 overflow-hidden ${classeForme} ${TAILLES[taille]}`}>
        {photo.webp && <source srcSet={photo.webp} type="image/webp" />}
        <img src={photo.jpg ?? photo.webp ?? undefined} alt="" className="h-full w-full object-cover" />
      </picture>
    )
  }

  return (
    <span
      aria-hidden="true"
      className={`flex shrink-0 items-center justify-center font-semibold text-surface ${classeForme} ${classeFondAvatar(nom)} ${TAILLES[taille]}`}
    >
      {initiale(nom)}
    </span>
  )
}
