/**
 * Une page de produits pour les filtres/tri/pagination donnés — le coeur
 * de données de PageListeProduits.
 */
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { recupererProduits } from '../api/produits'
import type { ParametresListeProduits } from '../api/produits'

export function useProduits(parametres: ParametresListeProduits) {
  return useQuery({
    queryKey: ['produits', parametres],
    queryFn: () => recupererProduits(parametres),
    // Garde la page précédente affichée pendant le chargement de la
    // suivante, pour éviter un clignotement vers l'état de chargement à
    // chaque changement de filtre ou de page.
    placeholderData: keepPreviousData,
  })
}
