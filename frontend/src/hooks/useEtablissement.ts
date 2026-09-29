/**
 * Fiche d'un établissement unique (informations, domaines, utilisateurs
 * rattachés) — voir PageFicheEtablissement.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererEtablissement } from '../api/etablissements'

export function useEtablissement(id: number) {
  return useQuery({
    queryKey: ['etablissement', id],
    queryFn: () => recupererEtablissement(id),
  })
}
