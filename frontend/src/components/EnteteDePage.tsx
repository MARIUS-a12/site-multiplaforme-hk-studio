/**
 * En-tête de page du back-office (Étape 8, maquette de référence) : icône
 * de la section dans un carré arrondi coloré, titre, sous-titre explicatif
 * en une phrase, action principale à droite. Un seul composant partagé par
 * toutes les pages du back-office, pour que ce motif reste identique
 * partout plutôt que recopié à chaque écran.
 */
import type { ReactNode } from 'react'

type CouleurCarre = 'marine' | 'orange' | 'vert' | 'bleu' | 'violet'

const CLASSES_CARRE: Record<CouleurCarre, string> = {
  marine: 'bg-marine',
  orange: 'bg-orange',
  vert: 'bg-primaire',
  bleu: 'bg-bleu-info',
  violet: 'bg-violet',
}

export function EnteteDePage({
  icone,
  couleur = 'marine',
  titre,
  sousTitre,
  action,
}: {
  icone: ReactNode
  couleur?: CouleurCarre
  titre: string
  sousTitre: string
  action?: ReactNode
}) {
  return (
    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-center gap-3">
        <span
          aria-hidden="true"
          className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-white ${CLASSES_CARRE[couleur]}`}
        >
          {icone}
        </span>
        <div className="min-w-0">
          <h1 className="font-titre text-titre-page font-bold text-texte">{titre}</h1>
          <p className="text-corps text-texte-secondaire">{sousTitre}</p>
        </div>
      </div>

      {action}
    </div>
  )
}
