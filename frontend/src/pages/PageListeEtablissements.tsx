/**
 * Écran /etablissements — inventaire de la plateforme, réservé au
 * super-admin (voir RouteProtegee). Recherche et filtre par type en local :
 * le nombre d'établissements reste petit, pas besoin d'un aller-retour
 * serveur par frappe.
 */
import { Plus } from 'lucide-react'
import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import type { Etablissement, TypeEtablissement } from '../api/etablissements'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { GroupeSegmente } from '../components/GroupeSegmente'
import { useEtablissements } from '../hooks/useEtablissements'
import { useValeurDifferee } from '../hooks/useValeurDifferee'

const OPTIONS_TYPE: { valeur: TypeEtablissement | ''; libelle: string }[] = [
  { valeur: '', libelle: 'Tous' },
  { valeur: 'boutique', libelle: 'Boutiques' },
  { valeur: 'restaurant', libelle: 'Restaurants' },
]

export function PageListeEtablissements() {
  const [recherche, setRecherche] = useState('')
  const [type, setType] = useState<TypeEtablissement | ''>('')
  const rechercheDifferee = useValeurDifferee(recherche)
  const { data: etablissements, isPending, isError, error, refetch } = useEtablissements()

  const filtres = useMemo(() => {
    if (!etablissements) {
      return []
    }

    const rechercheNormalisee = rechercheDifferee.trim().toLowerCase()

    return etablissements.filter((etablissement) => {
      const correspondType = type === '' || etablissement.type === type
      const correspondRecherche =
        rechercheNormalisee === '' || etablissement.nom.toLowerCase().includes(rechercheNormalisee)

      return correspondType && correspondRecherche
    })
  }, [etablissements, rechercheDifferee, type])

  return (
    <div className="mx-auto max-w-4xl space-y-6">
      <div className="flex items-center justify-between gap-3">
        <h1 className="text-titre-page font-semibold text-texte">Établissements</h1>
        <Link
          to="/etablissements/nouveau"
          className="flex h-11 cursor-pointer items-center gap-1.5 rounded bg-primaire px-3 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
        >
          <Plus aria-hidden="true" size={20} strokeWidth={1.5} />
          <span className="hidden sm:inline">Nouvel établissement</span>
        </Link>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input
          type="search"
          placeholder="Rechercher un établissement…"
          value={recherche}
          onChange={(evenement) => setRecherche(evenement.target.value)}
          className="h-11 w-full rounded border border-bordure bg-surface px-3 text-corps text-texte transition-colors duration-150 focus:border-primaire focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 sm:max-w-xs"
        />
        <GroupeSegmente options={OPTIONS_TYPE} valeur={type} onChange={setType} />
      </div>

      {isPending && <EtatChargement />}

      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && (
        <ul className="divide-y divide-bordure border border-bordure">
          {filtres.map((etablissement) => (
            <LigneEtablissement key={etablissement.id} etablissement={etablissement} />
          ))}
          {filtres.length === 0 && (
            <li className="p-4 text-center text-corps text-texte-secondaire">
              Aucun établissement ne correspond.
            </li>
          )}
        </ul>
      )}
    </div>
  )
}

function LigneEtablissement({ etablissement }: { etablissement: Etablissement }) {
  const estActif = etablissement.statut === 'actif'

  return (
    <li>
      <Link
        to={`/etablissements/${etablissement.id}`}
        className="flex flex-col gap-2 p-4 transition-colors duration-150 hover:bg-surface-alt sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <span className="text-corps font-semibold text-texte">{etablissement.nom}</span>
          <span className="ml-2 text-petit text-texte-secondaire">
            {etablissement.type === 'restaurant' ? 'Restaurant' : 'Boutique'}
          </span>
        </div>
        <div className="flex items-center gap-3 text-petit text-texte-secondaire">
          <span>{etablissement.sous_domaine ?? '—'}</span>
          <span className="tabular-nums">
            {etablissement.produits_count ?? 0} produit{(etablissement.produits_count ?? 0) === 1 ? '' : 's'}
          </span>
          <span className="tabular-nums">
            {new Date(etablissement.created_at).toLocaleDateString('fr-FR')}
          </span>
          <span className="inline-flex items-center gap-1.5">
            <span
              aria-hidden="true"
              className={`h-1.5 w-1.5 rounded-full ${estActif ? 'bg-succes' : 'bg-bordure'}`}
            />
            {estActif ? 'Actif' : 'Inactif'}
          </span>
        </div>
      </Link>
    </li>
  )
}
