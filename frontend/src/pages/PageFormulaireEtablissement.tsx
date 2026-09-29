/**
 * Écran /etablissements/nouveau — création complète d'un établissement.
 * Le mot de passe généré n'est jamais montré ici : la création réussie
 * redirige vers la fiche, qui le révèle une seule fois (voir
 * PageFicheEtablissement).
 */
import { useState } from 'react'
import type { FormEvent } from 'react'
import { useMutation } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import type { TypeEtablissement } from '../api/etablissements'
import { creerEtablissement } from '../api/etablissements'
import { BoutonRetour } from '../components/BoutonRetour'
import { ChampSousDomaine } from '../components/ChampSousDomaine'
import { ChampTexte } from '../components/ChampTexte'
import { GroupeSegmente } from '../components/GroupeSegmente'
import { allerAuPremierChampEnErreur, extraireErreursChamps } from '../lib/erreursValidation'

const ORDRE_CHAMPS = [
  'nom',
  'type',
  'sous_domaine',
  'email',
  'telephone',
  'nom_administrateur',
  'email_administrateur',
]

const OPTIONS_TYPE: { valeur: TypeEtablissement; libelle: string }[] = [
  { valeur: 'boutique', libelle: 'Boutique' },
  { valeur: 'restaurant', libelle: 'Restaurant' },
]

function slugifier(texte: string): string {
  return texte
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

export function PageFormulaireEtablissement() {
  const navigate = useNavigate()

  const [nom, setNom] = useState('')
  const [type, setType] = useState<TypeEtablissement>('boutique')
  const [sousDomaine, setSousDomaine] = useState('')
  const [sousDomaineTouche, setSousDomaineTouche] = useState(false)
  const [email, setEmail] = useState('')
  const [telephone, setTelephone] = useState('')
  const [nomAdministrateur, setNomAdministrateur] = useState('')
  const [emailAdministrateur, setEmailAdministrateur] = useState('')
  const [erreurs, setErreurs] = useState<Record<string, string>>({})
  const [erreurGenerique, setErreurGenerique] = useState<string | null>(null)

  function changerNom(valeur: string) {
    setNom(valeur)
    // Tant que le commerçant n'a pas touché au sous-domaine lui-même, il
    // suit le nom — dès qu'il le modifie à la main, on ne l'écrase plus.
    if (!sousDomaineTouche) {
      setSousDomaine(slugifier(valeur))
    }
  }

  const creation = useMutation({
    mutationFn: creerEtablissement,
    onSuccess: ({ etablissement, motDePasseGenere }) => {
      navigate(`/etablissements/${etablissement.id}`, {
        state: { motDePasseGenere, messageSucces: `« ${etablissement.nom} » a été créé.` },
      })
    },
    onError: (erreur) => {
      const champs = extraireErreursChamps(erreur)
      setErreurs(champs)
      setErreurGenerique(
        Object.keys(champs).length === 0
          ? "Impossible de créer l'établissement. Vérifiez votre connexion et réessayez."
          : null,
      )
      allerAuPremierChampEnErreur(champs, ORDRE_CHAMPS)
    },
  })

  function soumettre(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    setErreurGenerique(null)
    creation.mutate({
      nom,
      type,
      sous_domaine: sousDomaine,
      email: email || null,
      telephone: telephone || null,
      nom_administrateur: nomAdministrateur,
      email_administrateur: emailAdministrateur,
    })
  }

  return (
    <div className="mx-auto max-w-2xl pb-4">
      <div className="mb-4 flex items-center gap-2">
        <BoutonRetour vers="/etablissements" />
        <h1 className="text-titre-page font-semibold text-texte">Nouvel établissement</h1>
      </div>

      <form onSubmit={soumettre} className="space-y-4">
        {erreurGenerique && (
          <p role="alert" className="border border-danger bg-surface p-3 text-corps text-danger">
            {erreurGenerique}
          </p>
        )}

        <ChampTexte id="nom" label="Nom de l'établissement" requis valeur={nom} onChange={changerNom} erreur={erreurs.nom} />

        <div>
          <span className="mb-1 block text-petit font-medium text-texte">
            Type
            <span aria-hidden="true" className="text-danger">
              {' '}
              *
            </span>
          </span>
          <GroupeSegmente id="type" options={OPTIONS_TYPE} valeur={type} onChange={setType} />
        </div>

        <ChampSousDomaine
          id="sous_domaine"
          valeur={sousDomaine}
          onChange={(valeur) => {
            setSousDomaineTouche(true)
            setSousDomaine(valeur)
          }}
          erreur={erreurs.sous_domaine}
        />

        <h2 className="text-titre-section font-semibold text-texte">Contact de l'établissement</h2>

        <ChampTexte id="email" label="Email (optionnel)" valeur={email} onChange={setEmail} erreur={erreurs.email} />
        <ChampTexte
          id="telephone"
          label="Téléphone (optionnel)"
          valeur={telephone}
          onChange={setTelephone}
          erreur={erreurs.telephone}
        />

        <h2 className="text-titre-section font-semibold text-texte">Compte administrateur</h2>
        <p className="text-petit text-texte-secondaire">
          Un mot de passe sera généré automatiquement et affiché une seule fois après la création.
        </p>

        <ChampTexte
          id="nom_administrateur"
          label="Nom de l'administrateur"
          requis
          valeur={nomAdministrateur}
          onChange={setNomAdministrateur}
          erreur={erreurs.nom_administrateur}
        />
        <ChampTexte
          id="email_administrateur"
          label="Email de l'administrateur"
          requis
          valeur={emailAdministrateur}
          onChange={setEmailAdministrateur}
          erreur={erreurs.email_administrateur}
        />

        <div className="sticky bottom-0 -mx-4 flex gap-3 border-t border-bordure bg-surface px-4 py-3 md:static md:mx-0 md:border-t-0 md:bg-transparent md:px-0 md:py-0">
          <button
            type="submit"
            disabled={creation.isPending}
            className="h-11 flex-1 cursor-pointer rounded bg-primaire text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {creation.isPending ? 'Création…' : "Créer l'établissement"}
          </button>
        </div>
      </form>
    </div>
  )
}
