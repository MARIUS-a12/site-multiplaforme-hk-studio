/**
 * "Mon compte" (Étape 7) : changement de mot de passe et de profil
 * (nom/email). Accessible à tout utilisateur authentifié, quel que soit son
 * rôle — voir CompteController côté API.
 */
import { client } from '../lib/client'

export type ModifierMotDePassePayload = {
  mot_de_passe_actuel: string
  nouveau_mot_de_passe: string
  nouveau_mot_de_passe_confirmation: string
}

export async function modifierMotDePasse(payload: ModifierMotDePassePayload): Promise<void> {
  await client.patch('/api/compte/mot-de-passe', payload)
}

export type ModifierProfilPayload = {
  nom: string
  email: string
  mot_de_passe_actuel?: string
}

export type ProfilModifie = {
  utilisateur: { id: number; nom: string; email: string }
}

export async function modifierProfil(payload: ModifierProfilPayload): Promise<ProfilModifie> {
  const { data } = await client.patch<ProfilModifie>('/api/compte/profil', payload)

  return data
}
