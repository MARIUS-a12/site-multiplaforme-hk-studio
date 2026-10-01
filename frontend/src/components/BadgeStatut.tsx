/**
 * Indicateur discret (point + mot) du statut d'un produit — brouillon,
 * publié ou archivé. Volontairement sobre : à l'inverse de PastilleStock,
 * ce n'est pas l'information que le commerçant vient chercher des yeux.
 */
import type { StatutProduit } from '../api/produits'
import { CLASSE_POINT_STATUT } from '../lib/statutProduit'

const LIBELLES: Record<StatutProduit, string> = {
  brouillon: 'Brouillon',
  publie: 'Publié',
  archive: 'Archivé',
}

export function BadgeStatut({ statut }: { statut: StatutProduit }) {
  return (
    <span className="inline-flex items-center gap-1.5 text-petit text-texte-secondaire">
      <span aria-hidden="true" className={`h-1.5 w-1.5 rounded-full ${CLASSE_POINT_STATUT[statut]}`} />
      {LIBELLES[statut]}
    </span>
  )
}
