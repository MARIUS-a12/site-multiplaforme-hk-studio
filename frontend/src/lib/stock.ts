/**
 * Règle de disponibilité affichée au commerçant, calculée à partir des
 * colonnes de stock d'un produit. Source unique utilisée par la pastille de
 * stock (PastilleStock) et par le compteur de ruptures de la bande de
 * statistiques (useStatistiquesProduits) — les deux doivent toujours dire
 * la même chose.
 */
import type { Produit } from '../api/produits'

export type EtatStock = {
  libelle: string
  niveau: 'succes' | 'alerte' | 'danger'
}

/**
 * En mode compte, la quantité affichable est le stock moins ce qui est déjà
 * réservé par des commandes en cours. En mode interrupteur (restaurant), il
 * n'y a pas de quantité : seul le booléen disponible compte.
 */
export function calculerEtatStock(produit: Produit): EtatStock {
  if (produit.mode_stock === 'interrupteur') {
    return produit.disponible
      ? { libelle: 'Disponible', niveau: 'succes' }
      : { libelle: 'Épuisé', niveau: 'danger' }
  }

  const disponible = produit.quantite_stock - produit.quantite_reservee

  if (disponible <= 0) {
    return { libelle: 'Épuisé', niveau: 'danger' }
  }

  if (disponible === 1) {
    return { libelle: 'Plus qu’1', niveau: 'alerte' }
  }

  return { libelle: `En stock : ${disponible}`, niveau: 'succes' }
}

export function estEnRupture(produit: Produit): boolean {
  return calculerEtatStock(produit).niveau === 'danger'
}
