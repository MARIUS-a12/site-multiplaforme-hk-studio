/**
 * Étape 10 — /admin/equipe : le commerçant gère son personnel lui-même.
 * Réservée à gerer_equipe (en pratique le seul admin_etablissement, voir
 * RolesEtPermissionsSeeder) — BarreLaterale ne montre même pas l'entrée à
 * qui n'a pas la permission, voir ENTREES[].permissions.
 *
 * Les garde-fous serveur (pas son propre rôle, pas sa propre désactivation,
 * au moins un administrateur actif) sont appliqués CÔTÉ API — cette page se
 * contente de les REFLÉTER en désactivant les contrôles de sa propre ligne,
 * jamais de les PORTER elle-même : un contournement direct de l'API serait
 * de toute façon refusé par ModifierMembreEquipe/ChangerStatutMembreEquipe.
 *
 * Correctif activation par code : l'administrateur ne choisit ni ne voit
 * JAMAIS le mot de passe d'un membre. La création ne demande que nom/email/
 * rôle et affiche un CODE D'ACTIVATION à transmettre (voir
 * BandeauCodeActivationGenere) ; "mot de passe oublié" devient "Générer un
 * nouveau code d'accès", avec le même principe.
 */
import { useState } from 'react'
import type { FormEvent } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { KeyRound, Users } from 'lucide-react'
import type { MembreEquipe, StatutMembreEquipe } from '../api/equipe'
import {
  changerStatutMembreEquipe,
  creerMembreEquipe,
  genererCodeActivationMembre,
  modifierMembreEquipe,
} from '../api/equipe'
import { BandeauCodeActivationGenere } from '../components/BandeauCodeActivationGenere'
import { BandeauSucces } from '../components/BandeauSucces'
import { Bascule } from '../components/Bascule'
import { EnteteDePage } from '../components/EnteteDePage'
import { EtatBouton } from '../components/EtatBouton'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { CLE_EQUIPE, useEquipe, useRolesEquipe } from '../hooks/useEquipe'
import { useMoi } from '../hooks/useMoi'
import { extraireErreursChamps } from '../lib/erreursValidation'

function formaterDerniereConnexion(date: string | null): string {
  return date ? new Date(date).toLocaleDateString('fr-FR') : 'Jamais connecté'
}

const CLASSES_CHAMP =
  'h-11 w-full rounded border bg-surface px-3 text-corps text-texte transition-colors focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1'

export function PageEquipe() {
  const { data: moi } = useMoi()
  const { data: membres, isPending, isError, error, refetch } = useEquipe()
  const { data: roles } = useRolesEquipe()
  const queryClient = useQueryClient()

  const [nom, setNom] = useState('')
  const [email, setEmail] = useState('')
  const [roleId, setRoleId] = useState<number | ''>('')
  const [erreurs, setErreurs] = useState<Record<string, string>>({})
  const [codeAffiche, setCodeAffiche] = useState<string | null>(null)
  const [messageSucces, setMessageSucces] = useState<string | null>(null)

  function invaliderEquipe() {
    queryClient.invalidateQueries({ queryKey: CLE_EQUIPE })
  }

  const creation = useMutation({
    mutationFn: creerMembreEquipe,
    onSuccess: ({ codeActivation }) => {
      setNom('')
      setEmail('')
      setRoleId('')
      setErreurs({})
      setCodeAffiche(codeActivation)
      invaliderEquipe()
    },
    onError: (erreur) => setErreurs(extraireErreursChamps(erreur)),
  })

  const modificationRole = useMutation({
    mutationFn: ({ id, nouveauRoleId }: { id: number; nouveauRoleId: number }) =>
      modifierMembreEquipe(id, { roleId: nouveauRoleId }),
    onSuccess: () => invaliderEquipe(),
  })

  const changementStatut = useMutation({
    mutationFn: ({ id, statut }: { id: number; statut: StatutMembreEquipe }) => changerStatutMembreEquipe(id, statut),
  })

  const generationCode = useMutation({
    mutationFn: genererCodeActivationMembre,
    onSuccess: (code) => {
      setCodeAffiche(code)
      invaliderEquipe()
    },
  })

  function soumettreCreation(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    if (!nom.trim() || !email.trim() || roleId === '') {
      return
    }
    creation.mutate({ nom: nom.trim(), email: email.trim(), roleId })
  }

  function demanderNouveauCode(membre: MembreEquipe) {
    const confirme = window.confirm(
      `Un nouveau code d'accès sera généré pour ${membre.nom}. Il devra l'utiliser sur /admin/activation pour choisir un nouveau mot de passe, et ses sessions en cours seront fermées.\n\nContinuer ?`,
    )

    if (confirme) {
      generationCode.mutate(membre.id)
    }
  }

  function demanderChangementStatut(membre: MembreEquipe, nouveauStatutActif: boolean) {
    const nouveauStatut: StatutMembreEquipe = nouveauStatutActif ? 'actif' : 'suspendu'

    if (nouveauStatut === 'suspendu') {
      const confirme = window.confirm(
        `${membre.nom} ne pourra plus se connecter et ses sessions en cours seront fermées. Réversible.\n\nDésactiver ${membre.nom} ?`,
      )
      if (!confirme) {
        return
      }
    }

    changementStatut.mutate(
      { id: membre.id, statut: nouveauStatut },
      {
        onSuccess: () => {
          invaliderEquipe()
          setMessageSucces(`${membre.nom} a été ${nouveauStatut === 'actif' ? 'réactivé(e)' : 'désactivé(e)'}.`)
        },
      },
    )
  }

  return (
    <div className="mx-auto max-w-4xl space-y-6">
      <EnteteDePage
        icone={<Users aria-hidden="true" size={22} strokeWidth={1.75} />}
        couleur="bleu"
        titre="Équipe"
        sousTitre="Gérez les comptes de votre personnel et leurs accès."
      />

      {messageSucces && <BandeauSucces message={messageSucces} onFermer={() => setMessageSucces(null)} />}

      {codeAffiche && <BandeauCodeActivationGenere code={codeAffiche} onFermer={() => setCodeAffiche(null)} />}

      <form
        onSubmit={soumettreCreation}
        className="grid gap-3 border border-bordure bg-surface p-4 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-start"
      >
        <div>
          <label htmlFor="nom" className="mb-1 block text-petit font-medium text-texte">
            Nom
          </label>
          <input
            id="nom"
            type="text"
            value={nom}
            onChange={(evenement) => setNom(evenement.target.value)}
            className={`${CLASSES_CHAMP} ${erreurs.nom ? 'border-danger' : 'border-bordure focus:border-primaire'}`}
          />
          {erreurs.nom && <p className="animate-entree-champ mt-1 text-petit text-danger">{erreurs.nom}</p>}
        </div>

        <div>
          <label htmlFor="email" className="mb-1 block text-petit font-medium text-texte">
            Email
          </label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(evenement) => setEmail(evenement.target.value)}
            className={`${CLASSES_CHAMP} ${erreurs.email ? 'border-danger' : 'border-bordure focus:border-primaire'}`}
          />
          {erreurs.email && <p className="animate-entree-champ mt-1 text-petit text-danger">{erreurs.email}</p>}
        </div>

        <div>
          <label htmlFor="role_id" className="mb-1 block text-petit font-medium text-texte">
            Rôle
          </label>
          <select
            id="role_id"
            value={roleId}
            onChange={(evenement) => setRoleId(evenement.target.value ? Number(evenement.target.value) : '')}
            className={`${CLASSES_CHAMP} ${erreurs.role_id ? 'border-danger' : 'border-bordure focus:border-primaire'}`}
          >
            <option value="">Choisir un rôle</option>
            {roles?.map((role) => (
              <option key={role.id} value={role.id}>
                {role.libelle}
              </option>
            ))}
          </select>
          {erreurs.role_id && <p className="animate-entree-champ mt-1 text-petit text-danger">{erreurs.role_id}</p>}
        </div>

        <button
          type="submit"
          disabled={creation.isPending}
          className="h-11 shrink-0 cursor-pointer rounded bg-primaire px-4 text-corps font-medium text-surface transition-[background-color,transform] hover:bg-primaire-fonce active:scale-[0.97] active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60 disabled:active:scale-100"
        >
          <EtatBouton chargement={creation.isPending}>Ajouter</EtatBouton>
        </button>
      </form>

      {isPending && <EtatChargement />}
      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && membres && membres.length === 0 && (
        <p className="text-corps text-texte-secondaire">Aucun membre pour le moment.</p>
      )}

      {!isPending && !isError && membres && membres.length > 0 && (
        <ul className="divide-y divide-bordure border border-bordure">
          {membres.map((membre) => {
            const estSoiMeme = membre.utilisateur_id === moi?.utilisateur.id

            return (
              <li key={membre.id} className="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="min-w-0">
                  <p className="text-corps font-medium text-texte">
                    {membre.nom} {estSoiMeme && <span className="text-petit text-texte-secondaire">(vous)</span>}
                    {!membre.mot_de_passe_defini && (
                      <span className="ml-2 rounded bg-alerte/10 px-1.5 py-0.5 text-[11px] font-medium text-alerte">
                        En attente d'activation
                      </span>
                    )}
                  </p>
                  <p className="truncate text-petit text-texte-secondaire">{membre.email}</p>
                  <p className="text-petit text-texte-secondaire">
                    Ajouté le {new Date(membre.ajoute_le).toLocaleDateString('fr-FR')} · Dernière connexion :{' '}
                    {formaterDerniereConnexion(membre.derniere_connexion)}
                  </p>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                  <select
                    value={membre.role.id}
                    disabled={estSoiMeme || modificationRole.isPending}
                    title={estSoiMeme ? 'Vous ne pouvez pas changer votre propre rôle.' : undefined}
                    onChange={(evenement) =>
                      modificationRole.mutate({ id: membre.id, nouveauRoleId: Number(evenement.target.value) })
                    }
                    className="h-11 rounded border border-bordure bg-surface px-3 text-corps text-texte transition-colors focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {roles?.map((role) => (
                      <option key={role.id} value={role.id}>
                        {role.libelle}
                      </option>
                    ))}
                  </select>

                  <Bascule
                    actif={membre.statut === 'actif'}
                    onChange={(actif) => demanderChangementStatut(membre, actif)}
                    libelleActif="Actif"
                    libelleInactif="Désactivé"
                    disabled={estSoiMeme || changementStatut.isPending}
                  />

                  <button
                    type="button"
                    onClick={() => demanderNouveauCode(membre)}
                    disabled={generationCode.isPending}
                    title="Générer un nouveau code d'accès"
                    aria-label={`Générer un nouveau code d'accès pour ${membre.nom}`}
                    className="flex h-11 w-11 cursor-pointer items-center justify-center rounded text-texte-secondaire transition-[background-color,color,transform] hover:bg-surface-alt hover:text-primaire active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    <KeyRound aria-hidden="true" size={20} strokeWidth={1.5} />
                  </button>
                </div>
              </li>
            )
          })}
        </ul>
      )}
    </div>
  )
}
