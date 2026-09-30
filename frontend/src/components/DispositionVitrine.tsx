/**
 * Disposition commune aux pages publiques de la vitrine (accueil, fiche
 * produit) — aucune authentification, aucun rapport avec CoquilleApplication
 * qui, elle, habille le back-office connecté. Juste un en-tête léger avec le
 * nom de l'établissement, qui ramène à l'accueil, et une zone de contenu.
 * Montée par le routeur (voir App.tsx), jamais directement par une page.
 */
import { Link, Outlet } from 'react-router-dom'
import { useEtablissementVitrine } from '../hooks/useEtablissementVitrine'

export function DispositionVitrine() {
  const { data: etablissement, isPending } = useEtablissementVitrine()

  return (
    <div className="min-h-screen bg-surface">
      <header className="sticky top-0 z-10 border-b border-bordure bg-surface px-4">
        <div className="mx-auto flex h-14 max-w-6xl items-center">
          {isPending ? (
            <div className="h-5 w-40 animate-pulse rounded bg-surface-alt" />
          ) : (
            <Link to="/" className="truncate text-titre-section font-semibold text-texte">
              {etablissement?.nom ?? 'Boutique'}
            </Link>
          )}
        </div>
      </header>

      <main className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  )
}
