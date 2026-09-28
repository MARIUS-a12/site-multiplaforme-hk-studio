/**
 * Appels HTTP liés à la session : connexion, déconnexion, et /api/moi
 * (utilisateur courant + établissement + permissions). Ce dernier sert à la
 * fois de garde d'authentification et de source pour l'en-tête de l'app.
 */
import { client } from '../lib/client'

export type Utilisateur = {
  id: number
  nom: string
  email: string
}

export type TypeEtablissement = 'boutique' | 'restaurant'

export type Moi = {
  utilisateur: Utilisateur
  etablissement: { id: number; nom: string; type: TypeEtablissement } | null
  role: string
  permissions: string[]
}

export async function connecter(email: string, motDePasse: string): Promise<void> {
  await client.post('/api/connexion', { email, mot_de_passe: motDePasse })
}

export async function deconnecter(): Promise<void> {
  await client.post('/api/deconnexion')
}

export async function recupererMoi(): Promise<Moi> {
  const { data } = await client.get<Moi>('/api/moi')

  return data
}
