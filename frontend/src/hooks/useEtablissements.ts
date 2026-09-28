/**
 * Liste des établissements de la plateforme, pour l'écran dédié au
 * super-admin (voir PageEtablissementsSuperAdmin).
 */
import { useQuery } from '@tanstack/react-query'
import { recupererEtablissements } from '../api/etablissements'

export function useEtablissements() {
  return useQuery({
    queryKey: ['etablissements'],
    queryFn: recupererEtablissements,
  })
}
