/**
 * Écran public "/" — accueil de la boutique, vu par n'importe quel
 * visiteur non connecté. Barre de recherche, filtre par catégorie, grille
 * de produits publiés. N'appelle que l'API vitrine (aucune authentification,
 * voir DispositionVitrine qui l'entoure).
 */
import { useState } from 'react'
import { Search } from 'lucide-react'
import { Bouton } from '../components/Bouton'
import { CarteProduitVitrine } from '../components/CarteProduitVitrine'
import { EnteteAccueilVitrine } from '../components/EnteteAccueilVitrine'
import { EtatErreur } from '../components/EtatErreur'
import { EtatVideVitrine } from '../components/EtatVideVitrine'
import { SquelettesGrilleVitrine } from '../components/SquelettesGrilleVitrine'
import { useCategoriesVitrine } from '../hooks/useCategoriesVitrine'
import { useEtablissementVitrine } from '../hooks/useEtablissementVitrine'
import { useProduitsVitrine } from '../hooks/useProduitsVitrine'
import { useValeurDifferee } from '../hooks/useValeurDifferee'
import { styleEntreeListe } from '../lib/animation'

export function PageAccueilVitrine() {
  const [recherche, setRecherche] = useState('')
  const [categorieId, setCategorieId] = useState<number | null>(null)
  const [page, setPage] = useState(1)

  const { data: etablissement } = useEtablissementVitrine()
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
    <div className="space-y-8">
      {etablissement && <EnteteAccueilVitrine etablissement={etablissement} />}

      <div className="space-y-4">
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
            className="h-12 w-full rounded-md border border-bordure bg-surface pl-10 pr-3 text-corps text-texte transition-colors focus:border-texte focus:outline focus:outline-2 focus:outline-texte focus:outline-offset-1"
          />
        </div>

        {categories && categories.length > 0 && (
          <div className="flex gap-4 overflow-x-auto border-b border-bordure pb-0">
            <button
              type="button"
              onClick={() => choisirCategorie(null)}
              aria-pressed={categorieId === null}
              className={`flex h-11 shrink-0 cursor-pointer items-center border-b-2 px-1 text-corps font-medium transition-[border-color,color,transform] active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1 ${
                categorieId === null
                  ? 'border-accent text-texte'
                  : 'border-transparent text-texte-secondaire hover:text-texte'
              }`}
            >
              Tout
            </button>
            {categories.map((categorie) => (
              <button
                key={categorie.id}
                type="button"
                onClick={() => choisirCategorie(categorie.id)}
                aria-pressed={categorieId === categorie.id}
                className={`flex h-11 shrink-0 cursor-pointer items-center border-b-2 px-1 text-corps font-medium transition-[border-color,color,transform] active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1 ${
                  categorieId === categorie.id
                    ? 'border-accent text-texte'
                    : 'border-transparent text-texte-secondaire hover:text-texte'
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
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            {data.data.map((produit, index) => (
              <div key={produit.id} className="animate-entree-carte" style={styleEntreeListe(index)}>
                <CarteProduitVitrine produit={produit} />
              </div>
            ))}
          </div>

          {data.meta.last_page > 1 && (
            <div className="flex items-center justify-between gap-3 pt-1 text-petit text-texte-secondaire">
              <span className="tabular-nums">
                Page {data.meta.current_page} sur {data.meta.last_page}
              </span>
              <div className="flex gap-2">
                <Bouton variante="secondaire" taille="petite" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                  Précédent
                </Bouton>
                <Bouton
                  variante="secondaire"
                  taille="petite"
                  disabled={page >= data.meta.last_page}
                  onClick={() => setPage((p) => p + 1)}
                >
                  Suivant
                </Bouton>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}
