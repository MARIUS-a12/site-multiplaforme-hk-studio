/**
 * Fondu court sur le contenu principal au changement de page — jamais sur
 * l'en-tête ni la navigation, qui n'entrent pas dans cet emballage.
 * key={pathname} force React à remonter la div à chaque nouvelle route, ce
 * qui rejoue l'animation d'entrée (CSS pur, aucune bibliothèque).
 */
import type { ReactNode } from 'react'
import { useLocation } from 'react-router-dom'

export function TransitionPage({ children }: { children: ReactNode }) {
  const location = useLocation()

  return (
    <div key={location.pathname} className="animate-entree-page">
      {children}
    </div>
  )
}
