/**
 * Écran /etablissements — inventaire de la plateforme, réservé au
 * super-admin (voir RouteProtegee). Recherche et filtre par type en local :
 * le nombre d'établissements reste petit, pas besoin d'un aller-retour
 * serveur par frappe.
 */
import { CreditCard, Plus, TriangleAlert } from 'lucide-react'
import { useMemo, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import type { Etablissement, TypeEtablissement } from '../api/etablissements'
import { Bouton } from '../components/Bouton'
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
  const navigate = useNavigate()

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
        <h1 className="text-titre-page font-bold text-texte">Établissements</h1>
        <Bouton
          variante="principal"
          icone={<Plus aria-hidden="true" size={20} strokeWidth={1.5} />}
          onClick={() => navigate('/etablissements/nouveau')}
        >
          <span className="hidden sm:inline">Nouvel établissement</span>
        </Bouton>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input
          type="search"
          placeholder="Rechercher un établissement…"
          value={recherche}
          onChange={(evenement) => setRecherche(evenement.target.value)}
          className="h-11 w-full rounded-md border border-bordure bg-surface px-3 text-corps text-texte transition-colors focus:border-primaire focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 sm:max-w-xs"
        />
        <GroupeSegmente options={OPTIONS_TYPE} valeur={type} onChange={setType} />
      </div>

      {isPending && <EtatChargement />}

      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && (
        <ul className="divide-y divide-bordure overflow-hidden rounded-lg border border-bordure bg-surface">
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

const LIBELLES_CHAMPS_MANQUANTS: Record<string, string> = {
  logo: 'le logo',
  couleur: 'la couleur',
  whatsapp: 'le numéro WhatsApp',
  horaires: 'les horaires',
}

function LigneEtablissement({ etablissement }: { etablissement: Etablissement }) {
  const navigate = useNavigate()
  const estActif = etablissement.statut === 'actif'
  const champsManquants = etablissement.identite_champs_manquants

  return (
    <li className="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
      <Link
        to={`/etablissements/${etablissement.id}`}
        className="flex-1 transition-colors hover:text-primaire"
      >
        <span className="text-corps font-semibold text-texte">{etablissement.nom}</span>
        <span className="ml-2 text-petit text-texte-secondaire">
          {etablissement.type === 'restaurant' ? 'Restaurant' : 'Boutique'}
        </span>
      </Link>

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
        <span
          title={etablissement.paiement_configure ? 'Paiement configuré' : 'Paiement non configuré'}
          className={`inline-flex items-center gap-1 ${etablissement.paiement_configure ? 'text-succes' : 'text-texte-secondaire'}`}
        >
          <CreditCard aria-hidden="true" size={14} strokeWidth={1.5} />
          {etablissement.paiement_configure ? 'Paiement' : 'Sans paiement'}
        </span>
        {champsManquants.length > 0 && (
          <span
            title={`Il manque : ${champsManquants.map((c) => LIBELLES_CHAMPS_MANQUANTS[c] ?? c).join(', ')}.`}
            className="inline-flex items-center gap-1 text-alerte"
          >
            <TriangleAlert aria-hidden="true" size={14} strokeWidth={1.5} />
            Vitrine incomplète
          </span>
        )}
        <Bouton variante="secondaire" taille="petite" onClick={() => navigate(`/etablissements/${etablissement.id}/identite`)}>
          Modifier l'identité
        </Bouton>
      </div>
    </li>
  )
}
