/**
 * Indicateur discret (point + mot) du statut d'un produit — brouillon,
 * publié ou archivé. Volontairement sobre : à l'inverse de PastilleStock,
 * ce n'est pas l'information que le commerçant vient chercher des yeux.
 */
import type { StatutProduit } from '../api/produits'

const LIBELLES: Record<StatutProduit, string> = {
  brouillon: 'Brouillon',
  publie: 'Publié',
  archive: 'Archivé',
}

// Discret à l'oeil (juste un point + un mot), contrairement à la pastille
// de stock qui doit sauter aux yeux : le statut est une information
// secondaire.
const CLASSES_POINT: Record<StatutProduit, string> = {
  brouillon: 'bg-bordure',
  publie: 'bg-succes',
  archive: 'bg-texte-secondaire',
}

export function BadgeStatut({ statut }: { statut: StatutProduit }) {
  return (
    <span className="inline-flex items-center gap-1.5 text-petit text-texte-secondaire">
      <span aria-hidden="true" className={`h-1.5 w-1.5 rounded-full ${CLASSES_POINT[statut]}`} />
      {LIBELLES[statut]}
    </span>
  )
}
