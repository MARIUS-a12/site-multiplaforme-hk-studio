/**
 * Vérification en direct de la disponibilité d'un sous-domaine, débattue
 * pour ne pas interroger l'API à chaque frappe. Désactivée tant que le
 * format n'est pas déjà valide (kebab-case) — inutile d'appeler l'API pour
 * une valeur que la soumission refusera de toute façon pour une autre
 * raison que la disponibilité.
 */
import { useQuery } from '@tanstack/react-query'
import { verifierDisponibiliteSousDomaine } from '../api/etablissements'
import { useValeurDifferee } from './useValeurDifferee'

export const FORMAT_SOUS_DOMAINE = /^[a-z0-9]+(-[a-z0-9]+)*$/

export function useDisponibiliteSousDomaine(sousDomaine: string) {
  const valeurDifferee = useValeurDifferee(sousDomaine, 400)
  const formatValide = FORMAT_SOUS_DOMAINE.test(valeurDifferee)

  return useQuery({
    queryKey: ['sous-domaine-disponible', valeurDifferee],
    queryFn: () => verifierDisponibiliteSousDomaine(valeurDifferee),
    enabled: formatValide,
  })
}
