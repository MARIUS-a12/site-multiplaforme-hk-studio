import { useQueries } from '@tanstack/react-query'
import { recupererProduits } from '../api/produits'
import { estEnRupture } from '../lib/stock'

/**
 * "Publiés" et "brouillons" viennent de meta.total (exact, quelle que soit
 * la taille du catalogue : c'est le serveur qui compte). "En rupture" n'est
 * pas un statut filtrable côté API — il faut donc examiner les produits
 * publiés eux-mêmes, plafonné à 100 (le maximum accepté par /api/produits).
 * Au-delà, le chiffre resterait exact pour publiés/brouillons mais
 * sous-compterait les ruptures : acceptable pour un catalogue de boutique à
 * ce stade, à revisiter si un établissement dépasse 100 produits publiés.
 */
export function useStatistiquesProduits() {
  const [publiesQuery, brouillonsQuery] = useQueries({
    queries: [
      {
        queryKey: ['produits-stats', 'publie'],
        queryFn: () => recupererProduits({ statut: 'publie', par_page: 100 }),
      },
      {
        queryKey: ['produits-stats', 'brouillon'],
        queryFn: () => recupererProduits({ statut: 'brouillon', par_page: 1 }),
      },
    ],
  })

  return {
    publies: publiesQuery.data?.meta.total ?? 0,
    brouillons: brouillonsQuery.data?.meta.total ?? 0,
    ruptures: publiesQuery.data?.data.filter(estEnRupture).length ?? 0,
    isPending: publiesQuery.isPending || brouillonsQuery.isPending,
    isError: publiesQuery.isError || brouillonsQuery.isError,
  }
}
