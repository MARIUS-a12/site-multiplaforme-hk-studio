/**
 * Liste des catégories de l'établissement courant, pour afficher leur nom
 * dans la liste de produits (voir PageListeProduits).
 */
import { useQuery } from '@tanstack/react-query'
import { recupererCategories } from '../api/categories'

export function useCategories() {
  return useQuery({
    queryKey: ['categories'],
    queryFn: recupererCategories,
    // Change rarement au fil d'une session : pas besoin de la
    // rafraîchir à chaque focus d'onglet comme la liste de produits.
    staleTime: 5 * 60 * 1000,
  })
}
