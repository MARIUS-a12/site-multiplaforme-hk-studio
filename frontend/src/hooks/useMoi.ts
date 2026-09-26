/**
 * Utilisateur courant, son établissement et ses permissions. Utilisé à la
 * fois comme garde d'authentification (RouteProtegee) et comme source pour
 * l'en-tête de l'app (CoquilleApplication).
 */
import { useQuery } from '@tanstack/react-query'
import { recupererMoi } from '../api/auth'

export const CLE_MOI = ['moi'] as const

export function useMoi() {
  return useQuery({
    queryKey: CLE_MOI,
    queryFn: recupererMoi,
    // Un 401 signifie "pas connecté" : ce n'est pas une panne réseau à
    // réessayer, la route protégée doit rediriger tout de suite.
    retry: false,
  })
}
