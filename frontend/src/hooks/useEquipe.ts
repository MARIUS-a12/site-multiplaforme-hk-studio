import { useQuery } from '@tanstack/react-query'
import { recupererEquipe, recupererRolesEquipe } from '../api/equipe'

export const CLE_EQUIPE = ['equipe']

export function useEquipe() {
  return useQuery({ queryKey: CLE_EQUIPE, queryFn: recupererEquipe })
}

export function useRolesEquipe() {
  return useQuery({
    queryKey: ['equipe', 'roles'],
    queryFn: recupererRolesEquipe,
    // La liste des rôles ne change jamais au fil d'une session.
    staleTime: 5 * 60 * 1000,
  })
}
