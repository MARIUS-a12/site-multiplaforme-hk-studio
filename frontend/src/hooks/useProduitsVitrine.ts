/**
 * Une page de la grille publique pour les filtres donnés — coeur de
 * données de PageAccueilVitrine.
 */
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { recupererProduitsVitrine } from '../api/vitrine'
import type { ParametresListeVitrine } from '../api/vitrine'

export function useProduitsVitrine(parametres: ParametresListeVitrine) {
  return useQuery({
    queryKey: ['vitrine-produits', parametres],
    queryFn: () => recupererProduitsVitrine(parametres),
    // Garde la page précédente affichée pendant le chargement de la
    // suivante, pour éviter un clignotement vers le squelette à chaque
    // changement de filtre ou de page.
    placeholderData: keepPreviousData,
  })
}
