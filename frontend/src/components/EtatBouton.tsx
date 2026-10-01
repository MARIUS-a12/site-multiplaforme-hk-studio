/**
 * Contenu d'un bouton pendant une mutation en cours : le libellé s'efface
 * (fondu) et un indicateur tournant prend sa place, MAIS la largeur du
 * bouton ne bouge pas — le libellé reste dans le flux, seule son opacité
 * change, l'indicateur est superposé par-dessus en position absolue.
 * Remplace l'ancien réflexe "Verbe…" (texte différent pendant le
 * chargement, qui faisait sauter la largeur du bouton).
 */
import { Loader2 } from 'lucide-react'
import type { ReactNode } from 'react'

export function EtatBouton({ chargement, children }: { chargement: boolean; children: ReactNode }) {
  return (
    <span className="relative inline-flex items-center justify-center gap-1.5">
      <span
        className={`inline-flex items-center gap-1.5 transition-opacity duration-rapide ease-apparition ${
          chargement ? 'opacity-0' : 'opacity-100'
        }`}
      >
        {children}
      </span>
      {chargement && (
        <Loader2 aria-hidden="true" size={20} strokeWidth={1.5} className="absolute animate-spin" />
      )}
    </span>
  )
}
