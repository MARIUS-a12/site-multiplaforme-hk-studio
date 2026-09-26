/**
 * Garde d'authentification : appelle /api/moi avant de rendre une route.
 * Redirige vers /connexion si la session est invalide (401), sinon monte
 * CoquilleApplication (en-tête) autour de la route demandée. Toutes les
 * routes protégées de App.tsx passent par ici.
 */
import { Navigate, Outlet } from 'react-router-dom'
import { useMoi } from '../hooks/useMoi'
import { CoquilleApplication } from './CoquilleApplication'

export function RouteProtegee() {
  const { data: moi, isPending, isError } = useMoi()

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
    return <Navigate to="/connexion" replace />
  }

  return (
    <CoquilleApplication moi={moi}>
      <Outlet />
    </CoquilleApplication>
  )
}
