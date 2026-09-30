/**
 * Écran public "/" — accueil de la boutique, vu par n'importe quel
 * visiteur non connecté. Barre de recherche, filtre par catégorie, grille
 * de produits publiés. N'appelle que l'API vitrine (aucune authentification,
 * voir DispositionVitrine qui l'entoure).
 */
import { useState } from 'react'
import { Search } from 'lucide-react'
import { CarteProduitVitrine } from '../components/CarteProduitVitrine'
import { EtatErreur } from '../components/EtatErreur'
import { EtatVideVitrine } from '../components/EtatVideVitrine'
import { SquelettesGrilleVitrine } from '../components/SquelettesGrilleVitrine'
import { useCategoriesVitrine } from '../hooks/useCategoriesVitrine'
import { useProduitsVitrine } from '../hooks/useProduitsVitrine'
import { useValeurDifferee } from '../hooks/useValeurDifferee'

export function PageAccueilVitrine() {
  const [recherche, setRecherche] = useState('')
  const [categorieId, setCategorieId] = useState<number | null>(null)
  const [page, setPage] = useState(1)

  const rechercheDifferee = useValeurDifferee(recherche)
  const { data: categories } = useCategoriesVitrine()

  const { data, isPending, isError, error, refetch } = useProduitsVitrine({
    recherche: rechercheDifferee || undefined,
    categorie: categorieId ?? undefined,
    page,
  })

  function choisirCategorie(id: number | null) {
    setCategorieId(id)
    setPage(1)
  }

  return (
    <div className="space-y-6">
      <div className="space-y-3">
        <div className="relative max-w-md">
          <Search
            aria-hidden="true"
            size={20}
            strokeWidth={1.5}
            className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-texte-secondaire"
          />
          <input
            type="search"
            placeholder="Rechercher un produit…"
            value={recherche}
            onChange={(evenement) => {
              setRecherche(evenement.target.value)
              setPage(1)
            }}
            className="h-11 w-full rounded border border-bordure bg-surface pl-10 pr-3 text-corps text-texte transition-colors duration-150 focus:border-primaire focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1"
          />
        </div>

        {categories && categories.length > 0 && (
          <div className="flex gap-2 overflow-x-auto pb-1">
            <button
              type="button"
              onClick={() => choisirCategorie(null)}
              aria-pressed={categorieId === null}
              className={`h-9 shrink-0 cursor-pointer rounded-full border px-3 text-petit font-medium transition-colors duration-150 ${
                categorieId === null
                  ? 'border-primaire bg-primaire text-surface'
                  : 'border-bordure text-texte hover:bg-surface-alt'
              }`}
            >
              Toutes
            </button>
            {categories.map((categorie) => (
              <button
                key={categorie.id}
                type="button"
                onClick={() => choisirCategorie(categorie.id)}
                aria-pressed={categorieId === categorie.id}
                className={`h-9 shrink-0 cursor-pointer rounded-full border px-3 text-petit font-medium transition-colors duration-150 ${
                  categorieId === categorie.id
                    ? 'border-primaire bg-primaire text-surface'
                    : 'border-bordure text-texte hover:bg-surface-alt'
                }`}
              >
                {categorie.nom}
              </button>
            ))}
          </div>
        )}
      </div>

      {isPending && <SquelettesGrilleVitrine />}

      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && data && data.data.length === 0 && <EtatVideVitrine />}

      {!isPending && !isError && data && data.data.length > 0 && (
        <>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {data.data.map((produit) => (
              <CarteProduitVitrine key={produit.id} produit={produit} />
            ))}
          </div>

          {data.meta.last_page > 1 && (
            <div className="flex items-center justify-between gap-3 pt-1 text-petit text-texte-secondaire">
              <span className="tabular-nums">
                Page {data.meta.current_page} sur {data.meta.last_page}
              </span>
              <div className="flex gap-2">
                <button
                  type="button"
                  disabled={page <= 1}
                  onClick={() => setPage((p) => p - 1)}
                  className="h-11 cursor-pointer rounded border border-bordure px-3 font-medium text-texte transition-colors duration-150 hover:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  Précédent
                </button>
                <button
                  type="button"
                  disabled={page >= data.meta.last_page}
                  onClick={() => setPage((p) => p + 1)}
                  className="h-11 cursor-pointer rounded border border-bordure px-3 font-medium text-texte transition-colors duration-150 hover:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  Suivant
                </button>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}
