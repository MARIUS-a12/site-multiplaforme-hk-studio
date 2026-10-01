/**
 * Coquille visuelle de l'espace super-admin — l'équivalent de
 * CoquilleApplication, mais pour un utilisateur qui n'appartient à aucun
 * établissement. Montée par RouteProtegee, jamais par une route du
 * routeur directement.
 */
import { LogOut, User } from 'lucide-react'
import type { ReactNode } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import type { Moi } from '../api/auth'
import { deconnecter } from '../api/auth'
import { Bouton } from './Bouton'
import { CLE_MOI } from '../hooks/useMoi'
import { TransitionPage } from './TransitionPage'

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
          <Bouton
            variante="secondaire"
            taille="petite"
            icone={<User aria-hidden="true" size={18} strokeWidth={1.5} />}
            onClick={() => navigate('/admin/compte')}
          >
            <span className="hidden sm:inline">Mon compte</span>
          </Bouton>
          <Bouton
            variante="secondaire"
            taille="petite"
            icone={<LogOut aria-hidden="true" size={18} strokeWidth={1.5} />}
            chargement={deconnexion.isPending}
            onClick={() => deconnexion.mutate()}
          >
            <span className="hidden sm:inline">Déconnexion</span>
          </Bouton>
        </div>
      </header>

      <main className="p-4">
        <TransitionPage>{children}</TransitionPage>
      </main>
    </div>
  )
}
