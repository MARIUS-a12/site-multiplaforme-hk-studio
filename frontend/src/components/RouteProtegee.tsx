/**
 * Garde d'authentification : appelle /api/moi avant de rendre une route.
 * Redirige vers /admin/connexion si la session est invalide (401), sinon monte
 * la coquille adaptée (en-tête) autour de la route demandée. Toutes les
 * routes protégées de App.tsx passent par ici.
 *
 * Cas particulier du super-admin : /api/moi renvoie etablissement: null
 * pour lui (il n'en a aucun — voir SessionController::moi() côté API).
 * Aucune des pages du back-office commerçant (produits, catégories...) n'a
 * de sens pour lui et elles échoueraient toutes en 400 (aucun
 * établissement à filtrer) : on le confine à /etablissements/*, quelle que
 * soit l'URL demandée — sauf /admin/compte (Étape 7), accessible à TOUT
 * utilisateur authentifié sans distinction de rôle, lui compris.
 */
import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useMoi } from '../hooks/useMoi'
import { CoquilleApplication } from './CoquilleApplication'
import { CoquilleSuperAdmin } from './CoquilleSuperAdmin'

const CHEMIN_COMPTE = '/admin/compte'

export function RouteProtegee() {
  const { data: moi, isPending, isError } = useMoi()
  const location = useLocation()

  if (isPending) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-6">
        <div className="h-12 animate-pulse rounded-md bg-surface-alt" />
      </div>
    )
  }

  // /api/moi renvoie 401 quand la session n'est plus valide (jamais connecté,
  // session expirée) : direction l'écran de connexion.
  if (isError || !moi) {
    return <Navigate to="/admin/connexion" replace />
  }

  if (moi.etablissement === null) {
    if (!location.pathname.startsWith('/etablissements') && location.pathname !== CHEMIN_COMPTE) {
      return <Navigate to="/etablissements" replace />
    }

    return (
      <CoquilleSuperAdmin moi={moi}>
        <Outlet />
      </CoquilleSuperAdmin>
    )
  }

  return (
    <CoquilleApplication moi={moi}>
      <Outlet />
    </CoquilleApplication>
  )
}
