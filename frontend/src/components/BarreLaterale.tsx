/**
 * Barre latérale du back-office (Étape 8, maquette de référence) : 260px
 * fixes, fond bleu marine, sur grand écran. Sous 1024px, devient un panneau
 * coulissant (translate-x, 200ms) ouvert par le bouton de BarreSuperieure,
 * avec un fond assombri derrière — fermeture au clic extérieur, à Échap, et
 * après sélection d'une entrée (voir onFermerMobile). N'affiche QUE les
 * pages qui existent réellement : Produits et Catégories aujourd'hui,
 * jamais une entrée morte pour une page absente (Commandes, Clients...).
 *
 * La carte de statut en bas reflète l'état RÉEL de l'établissement
 * (moi.etablissement.statut, voir SessionController::reponseMoi côté API) —
 * jamais une valeur codée en dur.
 */
import { FolderTree, Package, Store } from 'lucide-react'
import { useEffect } from 'react'
import { NavLink } from 'react-router-dom'
import type { Moi } from '../api/auth'

const LIBELLES_ESPACE: Record<string, string> = {
  admin_etablissement: 'Espace Administrateur',
  operateur: 'Espace Opérateur',
}

const ENTREES = [
  { vers: '/admin/produits', libelle: 'Produits', icone: Package, permissions: ['voir_catalogue', 'gerer_catalogue'] },
  { vers: '/admin/categories', libelle: 'Catégories', icone: FolderTree, permissions: ['voir_catalogue', 'gerer_catalogue'] },
]

export function BarreLaterale({
  moi,
  ouvertMobile,
  onFermerMobile,
}: {
  moi: Moi
  ouvertMobile: boolean
  onFermerMobile: () => void
}) {
  useEffect(() => {
    if (!ouvertMobile) {
      return
    }

    function surEchap(evenement: KeyboardEvent) {
      if (evenement.key === 'Escape') {
        onFermerMobile()
      }
    }

    document.addEventListener('keydown', surEchap)
    return () => document.removeEventListener('keydown', surEchap)
  }, [ouvertMobile, onFermerMobile])

  const nomEtablissement = moi.etablissement?.nom ?? 'Back-office'
  const enLigne = moi.etablissement?.statut === 'actif'

  return (
    <>
      {ouvertMobile && (
        <div
          role="presentation"
          onClick={onFermerMobile}
          className="fixed inset-0 z-30 animate-entree-fondu bg-texte/40 lg:hidden"
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col bg-marine transition-transform duration-normale ease-apparition lg:translate-x-0 ${
          ouvertMobile ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex items-center gap-3 border-b border-white/10 p-4">
          <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-orange">
            <Store aria-hidden="true" size={22} strokeWidth={1.75} className="text-white" />
          </span>
          <div className="min-w-0">
            <p className="truncate font-titre text-corps font-semibold text-white">{nomEtablissement}</p>
            <p className="truncate text-petit text-white/60">{LIBELLES_ESPACE[moi.role] ?? 'Espace back-office'}</p>
          </div>
        </div>

        <nav className="flex-1 space-y-1 overflow-y-auto p-3">
          {ENTREES.filter((entree) => entree.permissions.some((permission) => moi.permissions.includes(permission))).map(
            (entree) => (
              <NavLink
                key={entree.vers}
                to={entree.vers}
                onClick={onFermerMobile}
                className={({ isActive }) =>
                  `flex h-11 items-center gap-3 rounded-lg px-3 text-corps font-medium transition-colors ${
                    isActive ? 'bg-orange text-white' : 'text-white/70 hover:bg-white/10 hover:text-white'
                  }`
                }
              >
                <entree.icone aria-hidden="true" size={20} strokeWidth={1.75} />
                {entree.libelle}
              </NavLink>
            ),
          )}
        </nav>

        <div className="m-3 rounded-lg border border-white/10 bg-white/5 p-3">
          <p className="flex items-center gap-1.5 text-petit font-medium text-white">
            <span
              aria-hidden="true"
              className={`h-2 w-2 rounded-full ${enLigne ? 'bg-succes' : 'bg-danger'}`}
            />
            {enLigne ? 'Boutique en ligne' : 'Boutique suspendue'}
          </p>
          <p className="mt-0.5 text-petit text-white/60">
            {enLigne ? 'Tout fonctionne correctement.' : 'Le site public est inaccessible.'}
          </p>
        </div>
      </aside>
    </>
  )
}
