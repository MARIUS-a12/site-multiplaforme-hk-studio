/**
 * Revérifie le panier auprès du serveur (prix, disponibilité) à chaque
 * changement de contenu — jamais sur un changement de "prixVu" seul (exclu de
 * la clé) : le resynchroniser après coup (voir PagePanier) ne doit pas
 * redéclencher un aller-retour réseau en boucle.
 */
import { useQuery } from '@tanstack/react-query'
import type { LignePanier } from '../lib/panier'
import { verifierPanier } from '../api/vitrine'

export function useVerifierPanier(lignes: LignePanier[]) {
  const cle = lignes.map((ligne) => `${ligne.produitId}:${ligne.varianteId ?? ''}:${ligne.quantite}`).join(',')

  return useQuery({
    queryKey: ['panier-verification', cle],
    queryFn: () => verifierPanier(lignes),
    enabled: lignes.length > 0,
  })
}
