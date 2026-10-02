/**
 * Barre supérieure du back-office (Étape 8) : fond blanc, fine bordure
 * inférieure. Pas de champ de recherche globale — aucune recherche globale
 * n'existe côté API aujourd'hui, et un champ décoratif qui ne fait rien
 * n'a pas sa place ici. Pas d'icône de notifications non plus, pour la même
 * raison : aucun système de notifications n'existe. Le bouton de gauche
 * (menu) n'apparaît que sous 1024px, pour ouvrir la barre latérale en
 * panneau coulissant (voir BarreLaterale).
 */
import { Menu } from 'lucide-react'
import type { Moi } from '../api/auth'
import { MenuUtilisateur } from './MenuUtilisateur'

export function BarreSuperieure({ moi, onOuvrirMenuLateral }: { moi: Moi; onOuvrirMenuLateral: () => void }) {
  return (
    <header className="sticky top-0 z-10 flex h-16 items-center justify-between gap-3 border-b border-bordure bg-surface px-4">
      <button
        type="button"
        onClick={onOuvrirMenuLateral}
        aria-label="Ouvrir le menu de navigation"
        className="flex h-11 w-11 cursor-pointer items-center justify-center rounded-md text-texte-secondaire transition-colors hover:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 lg:hidden"
      >
        <Menu aria-hidden="true" size={22} strokeWidth={1.75} />
      </button>

      <div className="flex-1" />

      <MenuUtilisateur moi={moi} />
    </header>
  )
}
