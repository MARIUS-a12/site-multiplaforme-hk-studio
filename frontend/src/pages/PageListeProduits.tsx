/**
 * Écran principal du back-office — route protégée "/". Bande de
 * statistiques (publiés/en rupture/brouillons), barre de recherche et de
 * filtres, puis la liste des produits elle-même : cartes empilées sous
 * 768px, tableau triable au-dessus. Ne fait ni création ni modification de
 * produit — ça arrive à une étape suivante.
 */
import { ArrowDown, ArrowUp } from 'lucide-react'
import { useState } from 'react'
import { BadgeStatut } from '../components/BadgeStatut'
import { BandeStatistiques } from '../components/BandeStatistiques'
import { BarreFiltres } from '../components/BarreFiltres'
import { CarreInitiale } from '../components/CarreInitiale'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { EtatVide } from '../components/EtatVide'
import { PastilleStock } from '../components/PastilleStock'
import { useCategories } from '../hooks/useCategories'
import { useProduits } from '../hooks/useProduits'
import { useValeurDifferee } from '../hooks/useValeurDifferee'
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

  const { data, isPending, isError, refetch, isFetching } = useProduits({
    recherche: rechercheDifferee || undefined,
    statut: statut || undefined,
    tri,
    direction,
    page,
  })

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
      <h1 className="text-titre-page font-semibold text-texte">Produits</h1>

      <BandeStatistiques />

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

      {isPending && <EtatChargement />}

      {isError && <EtatErreur onReessayer={() => refetch()} />}

      {!isPending && !isError && data && data.data.length === 0 && <EtatVide />}

      {!isPending && !isError && data && data.data.length > 0 && (
        <>
          {/* Mobile : une carte par produit. */}
          <ul className="space-y-3 md:hidden">
            {data.data.map((produit) => {
              const categorie = nomCategorie(produit)

              return (
                <li
                  key={produit.id}
                  className="flex items-start gap-3 border border-bordure bg-surface p-4 transition-colors duration-150 hover:bg-surface-alt"
                >
                  <CarreInitiale nom={produit.nom} taille={48} />
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
              </tr>
            </thead>
            <tbody>
              {data.data.map((produit) => {
                const categorie = nomCategorie(produit)

                return (
                  <tr key={produit.id} className="transition-colors duration-150 hover:bg-surface-alt">
                    <td className="border-b border-bordure px-4 py-3">
                      <div className="flex items-center gap-3">
                        <CarreInitiale nom={produit.nom} taille={40} />
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
