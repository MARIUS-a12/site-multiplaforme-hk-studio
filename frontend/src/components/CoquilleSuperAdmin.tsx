/**
 * Coquille visuelle de l'espace super-admin — l'équivalent de
 * CoquilleApplication, mais pour un utilisateur qui n'appartient à aucun
 * établissement. Montée par RouteProtegee, jamais par une route du
 * routeur directement.
 */
import { LogOut } from 'lucide-react'
import type { ReactNode } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import type { Moi } from '../api/auth'
import { deconnecter } from '../api/auth'
import { CLE_MOI } from '../hooks/useMoi'

export function CoquilleSuperAdmin({ moi, children }: { moi: Moi; children: ReactNode }) {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const deconnexion = useMutation({
    mutationFn: deconnecter,
    onSuccess: () => {
      queryClient.removeQueries({ queryKey: CLE_MOI })
      navigate('/admin/connexion', { replace: true })
    },
  })

  return (
    <div className="min-h-screen">
      <header className="sticky top-0 z-10 flex h-14 items-center justify-between border-b border-bordure bg-surface px-4">
        <span className="text-corps font-semibold text-texte">Administration de la plateforme</span>
        <div className="flex items-center gap-3">
          <span className="hidden truncate text-petit text-texte-secondaire sm:inline">
            {moi.utilisateur.nom}
          </span>
          <button
            type="button"
            onClick={() => deconnexion.mutate()}
            className="flex h-11 cursor-pointer items-center gap-1.5 rounded border border-bordure px-3 text-petit font-medium text-texte transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
          >
            <LogOut aria-hidden="true" size={20} strokeWidth={1.5} />
            <span className="hidden sm:inline">Déconnexion</span>
          </button>
        </div>
      </header>

      <main className="p-4">{children}</main>
    </div>
  )
}
