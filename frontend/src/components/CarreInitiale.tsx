import type { VarianteMedia } from '../api/medias'
import { classeFondAvatar, initiale } from '../lib/couleurAvatar'

const TAILLES = {
  40: 'h-10 w-10 text-base',
  48: 'h-12 w-12 text-lg',
} as const

/**
 * Vignette carrée : la photo principale du produit quand il en a une,
 * sinon l'initiale de son nom en repli. C'est le seul endroit à changer
 * pour ça — liste produits et en-tête n'ont rien à savoir de la présence ou
 * non d'une photo.
 */
export function CarreInitiale({
  nom,
  taille,
  photo = null,
}: {
  nom: string
  taille: 40 | 48
  photo?: VarianteMedia | null
}) {
  if (photo && (photo.webp || photo.jpg)) {
    return (
      <picture className={`block shrink-0 overflow-hidden rounded ${TAILLES[taille]}`}>
        {photo.webp && <source srcSet={photo.webp} type="image/webp" />}
        <img src={photo.jpg ?? photo.webp ?? undefined} alt="" className="h-full w-full object-cover" />
      </picture>
    )
  }

  return (
    <span
      aria-hidden="true"
      className={`flex shrink-0 items-center justify-center rounded font-semibold text-surface ${classeFondAvatar(nom)} ${TAILLES[taille]}`}
    >
      {initiale(nom)}
    </span>
  )
}
