/**
 * Nom, type, logo et numéro WhatsApp de l'établissement courant — l'en-tête
 * commun à toutes les pages de la vitrine, voir DispositionVitrine.
 */
import { useQuery } from '@tanstack/react-query'
import { recupererEtablissementVitrine } from '../api/vitrine'

export function useEtablissementVitrine() {
  return useQuery({
    queryKey: ['vitrine-etablissement'],
    queryFn: recupererEtablissementVitrine,
    staleTime: 5 * 60 * 1000,
  })
}
