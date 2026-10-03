/**
 * Étape 10 — équipe de l'établissement courant (/admin/equipe), réservée à
 * gerer_equipe. On ne supprime jamais un membre (voir le contrôleur côté
 * API) : seulement désactiver/réactiver, modifier nom/rôle, ou réinitialiser
 * son mot de passe.
 */
import { client } from '../lib/client'

export type StatutMembreEquipe = 'actif' | 'suspendu'

export type RoleEquipe = {
  id: number
  nom: string
  libelle: string
}

export type MembreEquipe = {
  id: number
  utilisateur_id: number
  nom: string
  email: string
  role: RoleEquipe
  statut: StatutMembreEquipe
  ajoute_le: string
  // null pour un membre qui ne s'est jamais encore connecté.
  derniere_connexion: string | null
}

export async function recupererEquipe(): Promise<MembreEquipe[]> {
  const { data } = await client.get<{ data: MembreEquipe[] }>('/api/equipe')

  return data.data
}

export async function recupererRolesEquipe(): Promise<RoleEquipe[]> {
  const { data } = await client.get<{ data: RoleEquipe[] }>('/api/equipe/roles')

  return data.data
}

export type NouveauMembreEquipe = {
  nom: string
  email: string
  roleId: number
}

export type MembreEquipeCree = {
  membre: MembreEquipe
  motDePasseGenere: string
}

export async function creerMembreEquipe(payload: NouveauMembreEquipe): Promise<MembreEquipeCree> {
  const { data } = await client.post<{ data: MembreEquipe; mot_de_passe_genere: string }>('/api/equipe', {
    nom: payload.nom,
    email: payload.email,
    role_id: payload.roleId,
  })

  return { membre: data.data, motDePasseGenere: data.mot_de_passe_genere }
}

export async function modifierMembreEquipe(
  id: number,
  payload: { nom?: string; roleId?: number },
): Promise<MembreEquipe> {
  const { data } = await client.put<{ data: MembreEquipe }>(`/api/equipe/${id}`, {
    nom: payload.nom,
    role_id: payload.roleId,
  })

  return data.data
}

export async function changerStatutMembreEquipe(id: number, statut: StatutMembreEquipe): Promise<MembreEquipe> {
  const { data } = await client.patch<{ data: MembreEquipe }>(`/api/equipe/${id}/statut`, { statut })

  return data.data
}

export async function reinitialiserMotDePasseMembreEquipe(id: number): Promise<string> {
  const { data } = await client.post<{ mot_de_passe_genere: string }>(`/api/equipe/${id}/reinitialiser-mot-de-passe`)

  return data.mot_de_passe_genere
}
