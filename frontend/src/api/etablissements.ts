/**
 * Liste des établissements de la plateforme — réservé au super-admin côté
 * API (voir EtablissementPolicy côté backend). N'est jamais appelé pour un
 * utilisateur rattaché à un établissement.
 */
import { client } from '../lib/client'

export type Etablissement = {
  id: number
  nom: string
  type: 'boutique' | 'restaurant'
  sous_domaine: string | null
  statut: string
}

export async function recupererEtablissements(): Promise<Etablissement[]> {
  const { data } = await client.get<{ data: Etablissement[] }>('/api/etablissements')

  return data.data
}
