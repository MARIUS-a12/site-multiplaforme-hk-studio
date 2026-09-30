/**
 * Une carte de la grille publique (voir PageAccueilVitrine) : photo,
 * nom, prix, pastille "Épuisé" si le produit n'est pas disponible. Mène à
 * la fiche produit. Sans photo, un repli neutre (icône) plutôt qu'une image
 * cassée.
 */
import { ImageOff } from 'lucide-react'
import { Link } from 'react-router-dom'
import type { ProduitVitrine } from '../api/vitrine'
import { formaterMontant } from '../lib/formatage'

export function CarteProduitVitrine({ produit }: { produit: ProduitVitrine }) {
  const { photo } = produit

  return (
    <Link
      to={`/produit/${produit.id}`}
      className="group block overflow-hidden rounded border border-bordure bg-surface transition-colors duration-150 hover:border-primaire focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
    >
      <div className="relative aspect-square w-full overflow-hidden bg-surface-alt">
        {photo ? (
          <picture>
            {(photo.vignette.webp || photo.moyenne.webp) && (
              <source
                type="image/webp"
                srcSet={[
                  photo.vignette.webp ? `${photo.vignette.webp} 150w` : null,
                  photo.moyenne.webp ? `${photo.moyenne.webp} 600w` : null,
                ]
                  .filter(Boolean)
                  .join(', ')}
                sizes="(max-width: 640px) 45vw, 300px"
              />
            )}
            <img
              src={photo.moyenne.jpg ?? photo.vignette.jpg ?? undefined}
              alt=""
              loading="lazy"
              width={300}
              height={300}
              className="h-full w-full object-cover"
            />
          </picture>
        ) : (
          <div className="flex h-full w-full items-center justify-center">
            <ImageOff aria-hidden="true" size={32} strokeWidth={1.5} className="text-texte-secondaire" />
          </div>
        )}

        {!produit.disponible && (
          <span className="absolute left-2 top-2 rounded bg-texte px-2 py-1 text-petit font-medium text-surface">
            Épuisé
          </span>
        )}
      </div>

      <div className={`p-3 ${produit.disponible ? '' : 'opacity-60'}`}>
        <p className="truncate text-corps font-medium text-texte">{produit.nom}</p>
        <p className="tabular-nums text-corps font-semibold text-texte">{formaterMontant(produit.prix)}</p>
      </div>
    </Link>
  )
}
