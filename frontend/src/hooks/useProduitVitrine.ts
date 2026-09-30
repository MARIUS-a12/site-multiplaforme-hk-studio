/**
 * La fiche complète d'un produit publié, pour PageFicheProduitVitrine.
 * id null (jamais en pratique ici, mais garde le même style que
 * useProduit du back-office) désactive la requête.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererProduitVitrine } from '../api/vitrine'

export function useProduitVitrine(id: number | null) {
  return useQuery({
    queryKey: ['vitrine-produit', id],
    queryFn: () => recupererProduitVitrine(id as number),
    enabled: id !== null,
  })
}
