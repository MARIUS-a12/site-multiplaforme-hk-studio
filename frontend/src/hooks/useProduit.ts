/**
 * Un produit unique, pour l'écran de modification (PageFormulaireProduit).
 * id est null à la création (rien à charger) : la requête reste désactivée
 * plutôt que d'être appelée avec un id inventé.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererProduit } from '../api/produits'

export function useProduit(id: number | null) {
  return useQuery({
    queryKey: ['produit', id],
    queryFn: () => recupererProduit(id as number),
    enabled: id !== null,
  })
}
