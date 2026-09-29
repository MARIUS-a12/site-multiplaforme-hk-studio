/**
 * Écran /etablissements/{id} — informations, domaines, utilisateurs
 * rattachés, et suspension/réactivation. Révèle aussi, une seule fois, le
 * mot de passe généré si on y arrive juste après une création (voir
 * PageFormulaireEtablissement) — jamais récupérable après ce premier
 * affichage.
 */
import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useLocation, useNavigate, useParams } from 'react-router-dom'
import { reactiverEtablissement, suspendreEtablissement } from '../api/etablissements'
import { BandeauMotDePasseGenere } from '../components/BandeauMotDePasseGenere'
import { BandeauSucces } from '../components/BandeauSucces'
import { BoutonRetour } from '../components/BoutonRetour'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { useEtablissement } from '../hooks/useEtablissement'

export function PageFicheEtablissement() {
  const { id } = useParams()
  const etablissementId = Number(id)
  const location = useLocation()
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const [motDePasseGenere, setMotDePasseGenere] = useState<string | null>(
    () => (location.state as { motDePasseGenere?: string } | null)?.motDePasseGenere ?? null,
  )
  const [messageSucces, setMessageSucces] = useState<string | null>(
    () => (location.state as { messageSucces?: string } | null)?.messageSucces ?? null,
  )

  function fermerBandeaux() {
    setMotDePasseGenere(null)
    setMessageSucces(null)
    navigate(location.pathname, { replace: true, state: null })
  }

  const { data: etablissement, isPending, isError, error, refetch } = useEtablissement(etablissementId)

  function appliquerMiseAJour(miseAJour: NonNullable<typeof etablissement>) {
    queryClient.setQueryData(['etablissement', etablissementId], miseAJour)
    queryClient.invalidateQueries({ queryKey: ['etablissements'] })
  }

  const suspension = useMutation({
    mutationFn: () => suspendreEtablissement(etablissementId),
    onSuccess: appliquerMiseAJour,
  })

  const reactivation = useMutation({
    mutationFn: () => reactiverEtablissement(etablissementId),
    onSuccess: appliquerMiseAJour,
  })

  function demanderSuspension() {
    const confirme = window.confirm(
      'Suspendre cet établissement rend son site public inaccessible et empêche ses utilisateurs de ' +
        's\'y connecter. Rien ne sera perdu, et vous pourrez le réactiver à tout moment.\n\n' +
        'Suspendre cet établissement ?',
    )

    if (confirme) {
      suspension.mutate()
    }
  }

  if (isPending) {
    return (
      <div className="mx-auto max-w-2xl">
        <BoutonRetour vers="/etablissements" />
        <EtatChargement />
      </div>
    )
  }

  if (isError) {
    return (
      <div className="mx-auto max-w-2xl">
        <BoutonRetour vers="/etablissements" />
        <EtatErreur erreur={error} onReessayer={() => refetch()} />
      </div>
    )
  }

  const estActif = etablissement.statut === 'actif'

  return (
    <div className="mx-auto max-w-2xl space-y-6 pb-4">
      <div className="flex items-center gap-2">
        <BoutonRetour vers="/etablissements" />
        <h1 className="text-titre-page font-semibold text-texte">{etablissement.nom}</h1>
      </div>

      {motDePasseGenere && (
        <BandeauMotDePasseGenere motDePasse={motDePasseGenere} onFermer={fermerBandeaux} />
      )}
      {!motDePasseGenere && messageSucces && (
        <BandeauSucces message={messageSucces} onFermer={fermerBandeaux} />
      )}

      <section className="border border-bordure">
        <h2 className="border-b border-bordure bg-surface-alt px-4 py-2 text-titre-section font-semibold text-texte">
          Informations
        </h2>
        <dl className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
          <Info libelle="Type" valeur={etablissement.type === 'restaurant' ? 'Restaurant' : 'Boutique'} />
          <Info libelle="Statut" valeur={estActif ? 'Actif' : 'Inactif'} />
          <Info libelle="Email" valeur={etablissement.email ?? '—'} />
          <Info libelle="Téléphone" valeur={etablissement.telephone ?? '—'} />
          <Info libelle="Créé le" valeur={new Date(etablissement.created_at).toLocaleDateString('fr-FR')} />
        </dl>
      </section>

      <section className="border border-bordure">
        <h2 className="border-b border-bordure bg-surface-alt px-4 py-2 text-titre-section font-semibold text-texte">
          Domaines
        </h2>
        <ul className="divide-y divide-bordure">
          {etablissement.domaines.map((domaine) => (
            <li key={domaine.id} className="flex items-center justify-between p-4 text-corps text-texte">
              <span>{domaine.hote}</span>
              <span className="text-petit text-texte-secondaire">
                {domaine.est_principal ? 'Principal' : 'Secondaire'}
              </span>
            </li>
          ))}
        </ul>
      </section>

      <section className="border border-bordure">
        <h2 className="border-b border-bordure bg-surface-alt px-4 py-2 text-titre-section font-semibold text-texte">
          Utilisateurs rattachés
        </h2>
        <ul className="divide-y divide-bordure">
          {etablissement.utilisateurs.map((utilisateur) => (
            <li
              key={utilisateur.id}
              className="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <span className="text-corps font-medium text-texte">{utilisateur.nom}</span>
                <span className="ml-2 text-petit text-texte-secondaire">{utilisateur.email}</span>
              </div>
              <span className="text-petit text-texte-secondaire">{utilisateur.role}</span>
            </li>
          ))}
          {etablissement.utilisateurs.length === 0 && (
            <li className="p-4 text-corps text-texte-secondaire">Aucun utilisateur rattaché.</li>
          )}
        </ul>
      </section>

      <div>
        {estActif ? (
          <button
            type="button"
            onClick={demanderSuspension}
            disabled={suspension.isPending}
            className="h-11 cursor-pointer rounded border border-danger px-4 text-corps font-medium text-danger transition-colors duration-150 hover:bg-danger/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {suspension.isPending ? 'Suspension…' : 'Suspendre cet établissement'}
          </button>
        ) : (
          <button
            type="button"
            onClick={() => reactivation.mutate()}
            disabled={reactivation.isPending}
            className="h-11 cursor-pointer rounded bg-primaire px-4 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {reactivation.isPending ? 'Réactivation…' : 'Réactiver cet établissement'}
          </button>
        )}
      </div>
    </div>
  )
}

function Info({ libelle, valeur }: { libelle: string; valeur: string }) {
  return (
    <div>
      <dt className="text-petit text-texte-secondaire">{libelle}</dt>
      <dd className="text-corps text-texte">{valeur}</dd>
    </div>
  )
}
