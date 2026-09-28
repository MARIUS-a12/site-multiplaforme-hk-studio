/**
 * Écran dédié au super-admin — il n'appartient à aucun établissement (ni
 * produits, ni catégories, ni commandes n'ont de sens pour lui), donc
 * aucune des pages du back-office commerçant ne doit jamais se monter pour
 * lui. Pour l'instant, un simple inventaire des établissements de la
 * plateforme ; l'interface complète (création d'établissement, etc.)
 * viendra plus tard. Monté directement par RouteProtegee, jamais par une
 * route du routeur : voir sa docblock.
 */
import { LogOut } from 'lucide-react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import type { Moi } from '../api/auth'
import { deconnecter } from '../api/auth'
import type { Etablissement } from '../api/etablissements'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { CLE_MOI } from '../hooks/useMoi'
import { useEtablissements } from '../hooks/useEtablissements'

export function PageEtablissementsSuperAdmin({ moi }: { moi: Moi }) {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { data: etablissements, isPending, isError, error, refetch } = useEtablissements()

  const deconnexion = useMutation({
    mutationFn: deconnecter,
    onSuccess: () => {
      queryClient.removeQueries({ queryKey: CLE_MOI })
      navigate('/connexion', { replace: true })
    },
  })

  return (
    <div className="min-h-screen">
      <header className="flex h-14 items-center justify-between border-b border-bordure bg-surface px-4">
        <span className="text-corps font-semibold text-texte">Administration de la plateforme</span>
        <div className="flex items-center gap-3">
          <span className="hidden truncate text-petit text-texte-secondaire sm:inline">
            {moi.utilisateur.nom}
          </span>
          <button
            type="button"
            onClick={() => deconnexion.mutate()}
            className="flex h-11 cursor-pointer items-center gap-1.5 rounded border border-bordure px-3 text-petit font-medium text-texte transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
          >
            <LogOut aria-hidden="true" size={20} strokeWidth={1.5} />
            <span className="hidden sm:inline">Déconnexion</span>
          </button>
        </div>
      </header>

      <main className="mx-auto max-w-3xl space-y-4 p-4">
        <div>
          <h1 className="text-titre-page font-semibold text-texte">Établissements</h1>
          <p className="mt-1 text-corps text-texte-secondaire">
            L'interface complète d'administration de la plateforme arrivera dans une prochaine
            étape. En attendant, voici les établissements existants.
          </p>
        </div>

        {isPending && <EtatChargement />}

        {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

        {!isPending && !isError && etablissements && (
          <ul className="divide-y divide-bordure border border-bordure">
            {etablissements.map((etablissement) => (
              <LigneEtablissement key={etablissement.id} etablissement={etablissement} />
            ))}
          </ul>
        )}
      </main>
    </div>
  )
}

function LigneEtablissement({ etablissement }: { etablissement: Etablissement }) {
  const estActif = etablissement.statut === 'actif'

  return (
    <li className="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <span className="text-corps font-semibold text-texte">{etablissement.nom}</span>
        <span className="ml-2 text-petit text-texte-secondaire">
          {etablissement.type === 'restaurant' ? 'Restaurant' : 'Boutique'}
        </span>
      </div>
      <div className="flex items-center gap-3 text-petit text-texte-secondaire">
        <span>{etablissement.sous_domaine ?? '—'}</span>
        <span className="inline-flex items-center gap-1.5">
          <span
            aria-hidden="true"
            className={`h-1.5 w-1.5 rounded-full ${estActif ? 'bg-succes' : 'bg-bordure'}`}
          />
          {estActif ? 'Actif' : 'Inactif'}
        </span>
      </div>
    </li>
  )
}
