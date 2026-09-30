/**
 * Route "/admin/*" (voir App.tsx) : toute URL du back-office qui ne
 * correspond à aucune page connue. Rendue à l'intérieur de RouteProtegee —
 * un visiteur non connecté qui tape une URL /admin/* inconnue est donc
 * d'abord redirigé vers /admin/connexion, jamais montré cette page ni la
 * vitrine publique.
 */
import { SearchX } from 'lucide-react'
import { Link } from 'react-router-dom'

export function PageIntrouvableAdmin() {
  return (
    <div className="flex flex-col items-center gap-3 border border-bordure bg-surface px-6 py-16 text-center">
      <SearchX aria-hidden="true" size={48} strokeWidth={1.5} className="text-texte-secondaire" />
      <h1 className="text-titre-section font-semibold text-texte">Page introuvable</h1>
      <p className="max-w-sm text-corps text-texte-secondaire">
        Cette page du back-office n'existe pas ou plus.
      </p>
      <Link
        to="/admin/produits"
        className="mt-2 inline-flex h-11 cursor-pointer items-center rounded bg-primaire px-4 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
      >
        Retour aux produits
      </Link>
    </div>
  )
}
