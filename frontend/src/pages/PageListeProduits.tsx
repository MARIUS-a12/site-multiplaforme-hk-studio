/**
 * Écran principal du back-office — route protégée "/". Bande de
 * statistiques (publiés/en rupture/brouillons), barre de recherche et de
 * filtres, puis la liste des produits elle-même : cartes empilées sous
 * 768px, tableau triable au-dessus. Chaque ligne porte ses propres actions
 * (modifier, archiver/republier) ; la création passe par le bouton
 * "Ajouter un produit".
 */
import { Archive, ArchiveRestore, ArrowDown, ArrowUp, Pencil, Plus } from 'lucide-react'
import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { archiverProduit, republierProduit } from '../api/produits'
import { BadgeStatut } from '../components/BadgeStatut'
import { BandeauSucces } from '../components/BandeauSucces'
import { BandeStatistiques } from '../components/BandeStatistiques'
import { BarreFiltres } from '../components/BarreFiltres'
import { CarreInitiale } from '../components/CarreInitiale'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { EtatVide } from '../components/EtatVide'
import { PastilleStock } from '../components/PastilleStock'
import { useCategories } from '../hooks/useCategories'
import { useMoi } from '../hooks/useMoi'
import { useProduits } from '../hooks/useProduits'
import { useValeurDifferee } from '../hooks/useValeurDifferee'
import { confirmerArchivageProduit } from '../lib/confirmations'
import { formaterMontant } from '../lib/formatage'
import type { ColonneTri, Produit, StatutProduit } from '../api/produits'

export function PageListeProduits() {
  const [recherche, setRecherche] = useState('')
  const [statut, setStatut] = useState<StatutProduit | ''>('')
  const [tri, setTri] = useState<ColonneTri>('date')
  const [direction, setDirection] = useState<'asc' | 'desc'>('desc')
  const [page, setPage] = useState(1)

  const rechercheDifferee = useValeurDifferee(recherche)
  const { data: categories } = useCategories()
  const { data: moi } = useMoi()
  const peutGererCatalogue = moi?.permissions.includes('gerer_catalogue') ?? false
  const navigate = useNavigate()
  const location = useLocation()
  const queryClient = useQueryClient()

  // Capturé une seule fois, à l'arrivée sur cette page (création,
  // modification ou archivage réussis redirigent ici avec ce message dans
  // l'état de navigation) — voir onFermer plus bas pour le nettoyage.
  const [messageSucces, setMessageSucces] = useState<string | null>(
    () => (location.state as { messageSucces?: string } | null)?.messageSucces ?? null,
  )

  function fermerBandeauSucces() {
    setMessageSucces(null)
    // Retire messageSucces de l'historique : sans ça, un rechargement de
    // page réafficherait le même bandeau.
    navigate(location.pathname, { replace: true, state: null })
  }

  const { data, isPending, isError, error, refetch, isFetching } = useProduits({
    recherche: rechercheDifferee || undefined,
    statut: statut || undefined,
    tri,
    direction,
    page,
  })

  function invaliderApresAction() {
    queryClient.invalidateQueries({ queryKey: ['produits'] })
    queryClient.invalidateQueries({ queryKey: ['produits-stats'] })
    queryClient.invalidateQueries({ queryKey: ['categories'] })
  }

  const archivage = useMutation({ mutationFn: (id: number) => archiverProduit(id) })
  const republication = useMutation({ mutationFn: (id: number) => republierProduit(id) })

  function demanderArchivage(produit: Produit) {
    if (!confirmerArchivageProduit()) {
      return
    }

    archivage.mutate(produit.id, {
      onSuccess: () => {
        invaliderApresAction()
        setMessageSucces(`« ${produit.nom} » a été archivé.`)
      },
    })
  }

  function republier(produit: Produit) {
    republication.mutate(produit.id, {
      onSuccess: () => {
        invaliderApresAction()
        setMessageSucces(`« ${produit.nom} » a été republié.`)
      },
    })
  }

  function nomCategorie(produit: Produit): string | null {
    return categories?.find((c) => c.id === produit.categorie_id)?.nom ?? null
  }

  function trierPar(colonne: ColonneTri) {
    if (tri === colonne) {
      setDirection((actuelle) => (actuelle === 'asc' ? 'desc' : 'asc'))
    } else {
      setTri(colonne)
      setDirection('asc')
    }
    setPage(1)
  }

  return (
    <div className="mx-auto max-w-5xl space-y-6">
      {messageSucces && <BandeauSucces message={messageSucces} onFermer={fermerBandeauSucces} />}

      <div className="flex items-center justify-between gap-3">
        <h1 className="text-titre-page font-semibold text-texte">Produits</h1>
        {peutGererCatalogue && (
          <Link
            to="/produits/nouveau"
            className="flex h-11 cursor-pointer items-center gap-1.5 rounded bg-primaire px-3 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
          >
            <Plus aria-hidden="true" size={20} strokeWidth={1.5} />
            <span className="hidden sm:inline">Ajouter un produit</span>
          </Link>
        )}
      </div>

      <BandeStatistiques />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <BarreFiltres
          recherche={recherche}
          onRechercheChange={(valeur) => {
            setRecherche(valeur)
            setPage(1)
          }}
          statut={statut}
          onStatutChange={(valeur) => {
            setStatut(valeur)
            setPage(1)
          }}
        />
        <Link to="/categories" className="text-petit text-primaire underline">
          Gérer les catégories
        </Link>
      </div>

      {isPending && <EtatChargement />}

      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && data && data.data.length === 0 && (
        <EtatVide peutCreer={peutGererCatalogue} />
      )}

      {!isPending && !isError && data && data.data.length > 0 && (
        <>
          {/* Mobile : une carte par produit, actions en bas. */}
          <ul className="space-y-3 md:hidden">
            {data.data.map((produit) => {
              const categorie = nomCategorie(produit)
              const estArchive = produit.statut === 'archive'

              return (
                <li key={produit.id} className="border border-bordure bg-surface p-4">
                  <div className="flex items-start gap-3">
                    <CarreInitiale nom={produit.nom} taille={48} photo={produit.medias[0]?.vignette} />
                    <div className="min-w-0 flex-1">
                      <span className="block truncate text-corps font-semibold text-texte">
                        {produit.nom}
                      </span>
                      <span className="tabular-nums text-corps font-medium text-texte">
                        {formaterMontant(produit.prix)}
                      </span>
                      <div className="mt-2 flex items-center justify-between gap-3">
                        <PastilleStock produit={produit} />
                        <span className="flex items-center gap-1.5 text-petit text-texte-secondaire">
                          <BadgeStatut statut={produit.statut} />
                          {categorie && <span>· {categorie}</span>}
                        </span>
                      </div>
                    </div>
                  </div>

                  {peutGererCatalogue && (
                    <div className="mt-3 flex gap-2 border-t border-bordure pt-3">
                      <Link
                        to={`/produits/${produit.id}/modifier`}
                        className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-bordure text-corps font-medium text-texte transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
                      >
                        <Pencil aria-hidden="true" size={20} strokeWidth={1.5} />
                        Modifier
                      </Link>
                      {estArchive ? (
                        <button
                          type="button"
                          onClick={() => republier(produit)}
                          disabled={republication.isPending}
                          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-primaire text-corps font-medium text-primaire transition-colors duration-150 hover:bg-primaire/10 active:bg-primaire/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                          <ArchiveRestore aria-hidden="true" size={20} strokeWidth={1.5} />
                          Republier
                        </button>
                      ) : (
                        <button
                          type="button"
                          onClick={() => demanderArchivage(produit)}
                          disabled={archivage.isPending}
                          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-danger text-corps font-medium text-danger transition-colors duration-150 hover:bg-danger/10 active:bg-danger/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                          <Archive aria-hidden="true" size={20} strokeWidth={1.5} />
                          Archiver
                        </button>
                      )}
                    </div>
                  )}
                </li>
              )
            })}
          </ul>

          {/* Écran large : tableau, en-têtes cliquables pour le tri. */}
          <table className="hidden w-full border-separate border-spacing-0 md:table">
            <thead>
              <tr className="bg-surface-alt text-left text-petit text-texte-secondaire">
                <EnteteTriable
                  libelle="Nom"
                  actif={tri === 'nom'}
                  direction={direction}
                  onClick={() => trierPar('nom')}
                />
                <th className="border-b border-bordure px-4 py-2 font-medium">Prix</th>
                <th className="border-b border-bordure px-4 py-2 font-medium">Stock</th>
                <th className="border-b border-bordure px-4 py-2 font-medium">Statut</th>
                <EnteteTriable
                  libelle="Ajouté le"
                  actif={tri === 'date'}
                  direction={direction}
                  onClick={() => trierPar('date')}
                />
                {peutGererCatalogue && (
                  <th className="border-b border-bordure px-4 py-2 font-medium">Actions</th>
                )}
              </tr>
            </thead>
            <tbody>
              {data.data.map((produit) => {
                const categorie = nomCategorie(produit)
                const estArchive = produit.statut === 'archive'

                return (
                  <tr key={produit.id} className="transition-colors duration-150 hover:bg-surface-alt">
                    <td className="border-b border-bordure px-4 py-3">
                      <div className="flex items-center gap-3">
                        <CarreInitiale nom={produit.nom} taille={40} photo={produit.medias[0]?.vignette} />
                        <span className="text-corps font-semibold text-texte">{produit.nom}</span>
                      </div>
                    </td>
                    <td className="border-b border-bordure px-4 py-3 tabular-nums text-corps font-medium text-texte">
                      {formaterMontant(produit.prix)}
                    </td>
                    <td className="border-b border-bordure px-4 py-3">
                      <PastilleStock produit={produit} />
                    </td>
                    <td className="border-b border-bordure px-4 py-3">
                      <div className="flex items-center gap-1.5 text-petit text-texte-secondaire">
                        <BadgeStatut statut={produit.statut} />
                        {categorie && <span>· {categorie}</span>}
                      </div>
                    </td>
                    <td className="border-b border-bordure px-4 py-3 tabular-nums text-petit text-texte-secondaire">
                      {new Date(produit.created_at).toLocaleDateString('fr-FR')}
                    </td>
                    {peutGererCatalogue && (
                      <td className="border-b border-bordure px-4 py-3">
                        <div className="flex items-center gap-1">
                          <Link
                            to={`/produits/${produit.id}/modifier`}
                            title="Modifier"
                            aria-label={`Modifier ${produit.nom}`}
                            className="flex h-11 w-11 cursor-pointer items-center justify-center rounded text-texte-secondaire transition-colors duration-150 hover:bg-surface-alt hover:text-texte focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
                          >
                            <Pencil aria-hidden="true" size={20} strokeWidth={1.5} />
                          </Link>
                          {estArchive ? (
                            <button
                              type="button"
                              title="Republier"
                              aria-label={`Republier ${produit.nom}`}
                              onClick={() => republier(produit)}
                              disabled={republication.isPending}
                              className="flex h-11 w-11 cursor-pointer items-center justify-center rounded text-texte-secondaire transition-colors duration-150 hover:bg-surface-alt hover:text-primaire focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                              <ArchiveRestore aria-hidden="true" size={20} strokeWidth={1.5} />
                            </button>
                          ) : (
                            <button
                              type="button"
                              title="Archiver"
                              aria-label={`Archiver ${produit.nom}`}
                              onClick={() => demanderArchivage(produit)}
                              disabled={archivage.isPending}
                              className="flex h-11 w-11 cursor-pointer items-center justify-center rounded text-texte-secondaire transition-colors duration-150 hover:bg-surface-alt hover:text-danger focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                              <Archive aria-hidden="true" size={20} strokeWidth={1.5} />
                            </button>
                          )}
                        </div>
                      </td>
                    )}
                  </tr>
                )
              })}
            </tbody>
          </table>

          <div className="flex items-center justify-between gap-3 pt-1 text-petit text-texte-secondaire">
            <span className="tabular-nums">
              Page {data.meta.current_page} sur {data.meta.last_page} — {data.meta.total} produits
            </span>
            <div className="flex gap-2">
              <button
                type="button"
                disabled={page <= 1 || isFetching}
                onClick={() => setPage((p) => p - 1)}
                className="h-11 cursor-pointer rounded border border-bordure px-3 font-medium text-texte transition-colors duration-150 hover:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-40"
              >
                Précédent
              </button>
              <button
                type="button"
                disabled={page >= data.meta.last_page || isFetching}
                onClick={() => setPage((p) => p + 1)}
                className="h-11 cursor-pointer rounded border border-bordure px-3 font-medium text-texte transition-colors duration-150 hover:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-40"
              >
                Suivant
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  )
}

function EnteteTriable({
  libelle,
  actif,
  direction,
  onClick,
}: {
  libelle: string
  actif: boolean
  direction: 'asc' | 'desc'
  onClick: () => void
}) {
  const Icone = direction === 'asc' ? ArrowUp : ArrowDown

  return (
    <th className="border-b border-bordure px-4 py-2 font-medium">
      <button
        type="button"
        onClick={onClick}
        className="flex cursor-pointer items-center gap-1 text-texte-secondaire focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
      >
        {libelle}
        {actif && <Icone aria-hidden="true" size={16} strokeWidth={1.5} />}
      </button>
    </th>
  )
}
