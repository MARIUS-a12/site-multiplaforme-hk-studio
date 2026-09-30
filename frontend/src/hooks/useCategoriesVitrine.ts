/**
 * Catégories publiques (celles qui contiennent au moins un produit publié),
 * pour les filtres de PageAccueilVitrine.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererCategoriesVitrine } from '../api/vitrine'

export function useCategoriesVitrine() {
  return useQuery({
    queryKey: ['vitrine-categories'],
    queryFn: recupererCategoriesVitrine,
    staleTime: 5 * 60 * 1000,
  })
}
