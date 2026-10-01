/**
 * Expose le panier (localStorage, voir lib/panier.ts) à tout composant sous
 * DispositionVitrine, synchronisé entre eux (l'en-tête, la fiche produit, la
 * page /panier) sans state global ni prop-drilling : useSyncExternalStore
 * relit lireLignesPanier() à chaque "panier:change" (déclenché par toute
 * écriture, voir lib/panier.ts) et à chaque "storage" (un autre onglet sur le
 * même établissement).
 */
import { useSyncExternalStore } from 'react'
import {
  ajouterLignePanier,
  definirPrixVuPanier,
  lireLignesPanier,
  modifierQuantitePanier,
  retirerLignePanier,
  viderPanier,
} from '../lib/panier'

function sabonner(callback: () => void): () => void {
  window.addEventListener('panier:change', callback)
  window.addEventListener('storage', callback)

  return () => {
    window.removeEventListener('panier:change', callback)
    window.removeEventListener('storage', callback)
  }
}

export function usePanier() {
  const lignes = useSyncExternalStore(sabonner, lireLignesPanier, lireLignesPanier)

  const nombreArticles = lignes.reduce((total, ligne) => total + ligne.quantite, 0)

  return {
    lignes,
    nombreArticles,
    // Fonctions déjà stables (déclarées au niveau module, voir lib/panier.ts)
    // : pas besoin de useCallback, leur référence ne change jamais.
    ajouter: ajouterLignePanier,
    modifierQuantite: modifierQuantitePanier,
    retirer: retirerLignePanier,
    definirPrixVu: definirPrixVuPanier,
    vider: viderPanier,
  }
}
