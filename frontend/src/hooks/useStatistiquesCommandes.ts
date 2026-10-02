/**
 * Statistiques réelles des commandes (Étape 9) : les quatre cartes de
 * PageListeCommandes ET la pastille "en attente" de la barre latérale
 * (BarreLaterale) partagent ce même hook/cette même clé de requête — une
 * seule donnée, deux affichages, jamais recalculée côté client. Rafraîchi
 * toutes les 60 secondes, uniquement quand l'onglet est visible (voir
 * useCommandes pour la même remarque sur refetchIntervalInBackground).
 */
import { useQuery } from '@tanstack/react-query'
import { recupererStatistiquesCommandes } from '../api/commandes'

export function useStatistiquesCommandes(actif: boolean) {
  return useQuery({
    queryKey: ['commandes-statistiques'],
    queryFn: recupererStatistiquesCommandes,
    refetchInterval: 60 * 1000,
    enabled: actif,
  })
}
