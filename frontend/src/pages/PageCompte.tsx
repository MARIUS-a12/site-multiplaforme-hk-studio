/**
 * "Mon compte" (Étape 7) — accessible à tout utilisateur authentifié, quel
 * que soit son rôle : changement de mot de passe (invalide les autres
 * sessions, garde la sienne) et de profil (nom/email, le second exigeant le
 * mot de passe actuel). Voir api/compte.ts et CompteController côté API.
 */
import axios from 'axios'
import { useState } from 'react'
import type { FormEvent } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { modifierMotDePasse, modifierProfil } from '../api/compte'
import { Bouton } from '../components/Bouton'
import { ChampTexte } from '../components/ChampTexte'
import { IndicateurForceMotDePasse } from '../components/IndicateurForceMotDePasse'
import { CLE_MOI, useMoi } from '../hooks/useMoi'
import { extraireErreursChamps } from '../lib/erreursValidation'

function messageErreurGenerique(erreur: unknown): string | null {
  if (axios.isAxiosError(erreur)) {
    if (erreur.response?.status === 429) {
      return 'Trop de tentatives. Réessayez dans une heure.'
    }
    if (erreur.response?.status === 422) {
      return null
    }
  }

  return 'Impossible de contacter le serveur. Vérifiez votre connexion et réessayez.'
}

function SectionMotDePasse() {
  const [motDePasseActuel, setMotDePasseActuel] = useState('')
  const [nouveauMotDePasse, setNouveauMotDePasse] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [erreurs, setErreurs] = useState<Record<string, string>>({})
  const [succesRecent, setSuccesRecent] = useState(false)

  const mutation = useMutation({
    mutationFn: () =>
      modifierMotDePasse({
        mot_de_passe_actuel: motDePasseActuel,
        nouveau_mot_de_passe: nouveauMotDePasse,
        nouveau_mot_de_passe_confirmation: confirmation,
      }),
    onSuccess: () => {
      setMotDePasseActuel('')
      setNouveauMotDePasse('')
      setConfirmation('')
      setErreurs({})
      setSuccesRecent(true)
    },
    onError: (erreur) => {
      setErreurs(extraireErreursChamps(erreur))
      setSuccesRecent(false)
    },
  })

  function soumettre(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    mutation.mutate()
  }

  const messageGenerique = mutation.isError ? messageErreurGenerique(mutation.error) : null

  return (
    <section className="rounded-lg border border-bordure bg-surface p-4 sm:p-5">
      <h2 className="text-titre-section font-semibold text-texte">Mot de passe</h2>
      <p className="mt-1 text-petit text-texte-secondaire">
        Après un changement, vos autres sessions ouvertes (un autre appareil, un autre navigateur) seront
        déconnectées. Celle-ci restera active.
      </p>

      <form onSubmit={soumettre} className="mt-4 space-y-4">
        <ChampTexte
          id="mot_de_passe_actuel"
          label="Mot de passe actuel"
          type="password"
          autoComplete="current-password"
          valeur={motDePasseActuel}
          onChange={setMotDePasseActuel}
          erreur={erreurs.mot_de_passe_actuel}
          requis
        />
        <div>
          <ChampTexte
            id="nouveau_mot_de_passe"
            label="Nouveau mot de passe"
            type="password"
            autoComplete="new-password"
            valeur={nouveauMotDePasse}
            onChange={setNouveauMotDePasse}
            erreur={erreurs.nouveau_mot_de_passe}
            requis
          />
          <IndicateurForceMotDePasse motDePasse={nouveauMotDePasse} />
        </div>
        <ChampTexte
          id="nouveau_mot_de_passe_confirmation"
          label="Confirmer le nouveau mot de passe"
          type="password"
          autoComplete="new-password"
          valeur={confirmation}
          onChange={setConfirmation}
          requis
        />

        {messageGenerique && (
          <p role="alert" className="animate-entree-champ text-petit text-danger">
            {messageGenerique}
          </p>
        )}

        {succesRecent && (
          <p role="status" className="animate-entree-champ text-petit text-succes">
            Mot de passe modifié. Vos autres sessions ont été déconnectées.
          </p>
        )}

        <Bouton type="submit" variante="principal" chargement={mutation.isPending}>
          Modifier le mot de passe
        </Bouton>
      </form>
    </section>
  )
}

function SectionProfil({ nomInitial, emailInitial }: { nomInitial: string; emailInitial: string }) {
  const [nom, setNom] = useState(nomInitial)
  const [email, setEmail] = useState(emailInitial)
  const [motDePasseActuel, setMotDePasseActuel] = useState('')
  const [erreurs, setErreurs] = useState<Record<string, string>>({})
  const [succesRecent, setSuccesRecent] = useState(false)
  const queryClient = useQueryClient()

  const emailModifie = email !== emailInitial

  const mutation = useMutation({
    mutationFn: () =>
      modifierProfil({
        nom,
        email,
        ...(emailModifie ? { mot_de_passe_actuel: motDePasseActuel } : {}),
      }),
    onSuccess: async () => {
      setMotDePasseActuel('')
      setErreurs({})
      setSuccesRecent(true)
      await queryClient.invalidateQueries({ queryKey: CLE_MOI })
    },
    onError: (erreur) => {
      setErreurs(extraireErreursChamps(erreur))
      setSuccesRecent(false)
    },
  })

  function soumettre(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    mutation.mutate()
  }

  const messageGenerique = mutation.isError ? messageErreurGenerique(mutation.error) : null

  return (
    <section className="rounded-lg border border-bordure bg-surface p-4 sm:p-5">
      <h2 className="text-titre-section font-semibold text-texte">Profil</h2>

      <form onSubmit={soumettre} className="mt-4 space-y-4">
        <ChampTexte id="nom" label="Nom" valeur={nom} onChange={setNom} erreur={erreurs.nom} requis />
        <ChampTexte
          id="email"
          label="Email"
          type="email"
          autoComplete="email"
          valeur={email}
          onChange={setEmail}
          erreur={erreurs.email}
          requis
        />

        {emailModifie && (
          <ChampTexte
            id="mot_de_passe_actuel"
            label="Mot de passe actuel"
            type="password"
            autoComplete="current-password"
            placeholder="Requis pour changer d'email"
            valeur={motDePasseActuel}
            onChange={setMotDePasseActuel}
            erreur={erreurs.mot_de_passe_actuel}
            requis
          />
        )}

        {messageGenerique && (
          <p role="alert" className="animate-entree-champ text-petit text-danger">
            {messageGenerique}
          </p>
        )}

        {succesRecent && (
          <p role="status" className="animate-entree-champ text-petit text-succes">
            Profil mis à jour.
          </p>
        )}

        <Bouton type="submit" variante="principal" chargement={mutation.isPending}>
          Enregistrer
        </Bouton>
      </form>
    </section>
  )
}

export function PageCompte() {
  const { data: moi, isPending } = useMoi()

  if (isPending || !moi) {
    return (
      <div className="mx-auto max-w-xl space-y-4">
        <div className="h-40 animate-pulse rounded-lg bg-surface-alt" />
        <div className="h-40 animate-pulse rounded-lg bg-surface-alt" />
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-xl space-y-6 pb-4">
      <h1 className="text-titre-page font-semibold text-texte">Mon compte</h1>

      <SectionProfil nomInitial={moi.utilisateur.nom} emailInitial={moi.utilisateur.email} />
      <SectionMotDePasse />
    </div>
  )
}
