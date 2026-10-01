/**
 * Bouton partagé à trois niveaux hiérarchiques (Étape 7) : principal (plein,
 * dense — l'action qu'on veut qu'on prenne), secondaire (contour marqué),
 * tertiaire (texte seul, le moins engageant). Avant ce composant, chaque
 * écran recopiait ses propres classes Tailwind pour un bouton — toute
 * nouvelle hiérarchie de boutons cohérente dans toute l'app passe
 * nécessairement par ici désormais. États : repos, survol, pression (scale
 * 0.97 + ombre qui s'aplatit sur le principal, pour une sensation de
 * matière), focus clavier, désactivé, chargement (voir EtatBouton).
 */
import { forwardRef } from 'react'
import type { ButtonHTMLAttributes, ReactNode } from 'react'
import { EtatBouton } from './EtatBouton'

type VarianteBouton = 'principal' | 'secondaire' | 'tertiaire' | 'danger'
type TailleBouton = 'normale' | 'petite'

type ProprietesBouton = ButtonHTMLAttributes<HTMLButtonElement> & {
  variante?: VarianteBouton
  taille?: TailleBouton
  chargement?: boolean
  icone?: ReactNode
  children: ReactNode
}

const CLASSES_COMMUNES =
  'inline-flex cursor-pointer items-center justify-center gap-2 rounded-md font-medium transition-[background-color,color,border-color,box-shadow,transform] duration-rapide ease-apparition active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100'

const CLASSES_VARIANTE: Record<VarianteBouton, string> = {
  principal:
    'bg-primaire text-surface shadow-bouton hover:bg-primaire-fonce active:bg-primaire-fonce active:shadow-none active:translate-y-px focus-visible:outline-primaire disabled:active:shadow-bouton disabled:active:translate-y-0',
  secondaire:
    'border-2 border-bordure-forte bg-surface text-texte hover:bg-surface-alt active:bg-surface-alt focus-visible:outline-primaire',
  tertiaire: 'text-texte hover:bg-surface-alt active:bg-surface-alt focus-visible:outline-primaire',
  danger:
    'border-2 border-danger text-danger hover:bg-danger/10 active:bg-danger/10 focus-visible:outline-danger',
}

const CLASSES_TAILLE: Record<TailleBouton, string> = {
  normale: 'h-12 px-5 text-corps',
  petite: 'h-11 px-3 text-petit',
}

export const Bouton = forwardRef<HTMLButtonElement, ProprietesBouton>(function Bouton(
  { variante = 'secondaire', taille = 'normale', chargement = false, icone, children, className, disabled, type = 'button', ...reste },
  ref,
) {
  return (
    <button
      ref={ref}
      type={type}
      disabled={disabled || chargement}
      className={[CLASSES_COMMUNES, CLASSES_VARIANTE[variante], CLASSES_TAILLE[taille], className]
        .filter(Boolean)
        .join(' ')}
      {...reste}
    >
      <EtatBouton chargement={chargement}>
        <span className="inline-flex items-center gap-2">
          {icone}
          {children}
        </span>
      </EtatBouton>
    </button>
  )
})
