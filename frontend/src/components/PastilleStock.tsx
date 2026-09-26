/**
 * Pastille colorée de stock d'un produit — l'élément le plus visible de la
 * liste après le nom, c'est ce que le commerçant vient chercher des yeux.
 * La règle de calcul (compte vs interrupteur, seuils) vit dans lib/stock.ts,
 * partagée avec la bande de statistiques.
 */
import type { Produit } from '../api/produits'
import { calculerEtatStock } from '../lib/stock'
import type { EtatStock } from '../lib/stock'

const CLASSES_NIVEAU: Record<EtatStock['niveau'], string> = {
  succes: 'bg-succes/10 text-succes',
  alerte: 'bg-alerte/10 text-alerte',
  danger: 'bg-danger/10 text-danger',
}

export function PastilleStock({ produit }: { produit: Produit }) {
  const etat = calculerEtatStock(produit)

  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded px-2.5 py-1 text-corps font-medium tabular-nums ${CLASSES_NIVEAU[etat.niveau]}`}
    >
      <span aria-hidden="true" className="h-2 w-2 rounded-full bg-current" />
      {etat.libelle}
    </span>
  )
}
