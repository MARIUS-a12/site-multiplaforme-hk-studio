/**
 * Correctif Étape 10 — /admin/activation, publique : un employé qui vient
 * d'être créé (ou dont l'administrateur a généré un nouveau code, voir
 * api/equipe.ts) choisit lui-même son mot de passe ici, une seule fois.
 */
import { client } from '../lib/client'

export type ActivationPayload = {
  email: string
  code: string
  nouveauMotDePasse: string
  nouveauMotDePasseConfirmation: string
}

export async function activerCompte(payload: ActivationPayload): Promise<void> {
  await client.post('/api/activation', {
    email: payload.email,
    code: payload.code,
    nouveau_mot_de_passe: payload.nouveauMotDePasse,
    nouveau_mot_de_passe_confirmation: payload.nouveauMotDePasseConfirmation,
  })
}
