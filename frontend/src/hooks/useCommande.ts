/**
 * Détail d'une commande — PageDetailCommande.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererCommande } from '../api/commandes'

export function useCommande(id: number) {
  return useQuery({
    queryKey: ['commande', id],
    queryFn: () => recupererCommande(id),
  })
}
