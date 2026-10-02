/**
 * Une page de commandes pour les filtres/pagination donnés — le cœur de
 * données de PageListeCommandes. Rafraîchi toutes les 60 secondes (Étape 9) :
 * une nouvelle commande arrivée pendant que la page est ouverte doit
 * apparaître sans action du commerçant. refetchIntervalInBackground vaut
 * false par défaut dans TanStack Query — cette seule ligne suffit donc à
 * respecter "uniquement quand l'onglet est visible", sans rien écrire de
 * plus : interrogé via document.visibilityState en coulisses.
 */
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { recupererCommandes } from '../api/commandes'
import type { ParametresListeCommandes } from '../api/commandes'

export function useCommandes(parametres: ParametresListeCommandes) {
  return useQuery({
    queryKey: ['commandes', parametres],
    queryFn: () => recupererCommandes(parametres),
    placeholderData: keepPreviousData,
    refetchInterval: 60 * 1000,
  })
}
