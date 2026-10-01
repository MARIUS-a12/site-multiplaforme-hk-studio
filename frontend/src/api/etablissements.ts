/**
 * Gestion des établissements de la plateforme — réservé au super-admin côté
 * API (voir EtablissementPolicy côté backend). N'est jamais appelé pour un
 * utilisateur rattaché à un établissement.
 */
import { client } from '../lib/client'

export type TypeEtablissement = 'boutique' | 'restaurant'

export type Etablissement = {
  id: number
  nom: string
  type: TypeEtablissement
  sous_domaine: string | null
  statut: string
  produits_count?: number
  created_at: string
}

export type Domaine = {
  id: number
  hote: string
  est_principal: boolean
  statut: string
}

export type UtilisateurRattache = {
  id: number
  nom: string
  email: string
  role: string
  statut: string
}

export type EtablissementDetail = {
  id: number
  nom: string
  type: TypeEtablissement
  statut: string
  email: string | null
  telephone: string | null
  couleur_accent: string | null
  created_at: string
  domaines: Domaine[]
  utilisateurs: UtilisateurRattache[]
}

export type NouvelEtablissementPayload = {
  nom: string
  type: TypeEtablissement
  sous_domaine: string
  email: string | null
  telephone: string | null
  couleur_accent: string | null
  nom_administrateur: string
  email_administrateur: string
}

export type EtablissementCree = {
  etablissement: EtablissementDetail
  motDePasseGenere: string
}

export async function recupererEtablissements(): Promise<Etablissement[]> {
  const { data } = await client.get<{ data: Etablissement[] }>('/api/etablissements')

  return data.data
}

export async function recupererEtablissement(id: number): Promise<EtablissementDetail> {
  const { data } = await client.get<{ data: EtablissementDetail }>(`/api/etablissements/${id}`)

  return data.data
}

/**
 * mot_de_passe_genere n'est présent que dans CETTE réponse — jamais dans la
 * liste ni dans la fiche récupérées ensuite (voir EtablissementController
 * côté backend). Le composant appelant est responsable de ne l'afficher
 * qu'une fois, jamais de le conserver au-delà de cet écran.
 */
export async function creerEtablissement(payload: NouvelEtablissementPayload): Promise<EtablissementCree> {
  const { data } = await client.post<{ data: EtablissementDetail; mot_de_passe_genere: string }>(
    '/api/etablissements',
    payload,
  )

  return { etablissement: data.data, motDePasseGenere: data.mot_de_passe_genere }
}

export async function suspendreEtablissement(id: number): Promise<EtablissementDetail> {
  const { data } = await client.post<{ data: EtablissementDetail }>(`/api/etablissements/${id}/suspendre`)

  return data.data
}

export async function reactiverEtablissement(id: number): Promise<EtablissementDetail> {
  const { data } = await client.post<{ data: EtablissementDetail }>(`/api/etablissements/${id}/reactiver`)

  return data.data
}

export async function verifierDisponibiliteSousDomaine(valeur: string): Promise<boolean> {
  const { data } = await client.get<{ disponible: boolean }>('/api/etablissements/sous-domaine-disponible', {
    params: { valeur },
  })

  return data.disponible
}
