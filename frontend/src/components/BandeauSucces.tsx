import { X } from 'lucide-react'

/**
 * Bandeau de confirmation après une action réussie (création, modification,
 * archivage), affiché en haut de la liste de produits. Se ferme au clic ;
 * la page qui le montre est responsable de vider l'état de navigation qui
 * le porte, pour qu'il ne réapparaisse pas au rechargement.
 */
export function BandeauSucces({ message, onFermer }: { message: string; onFermer: () => void }) {
  return (
    <div
      role="status"
      className="flex items-center justify-between gap-3 border border-succes bg-succes/10 px-4 py-3 text-corps text-succes"
    >
      <span>{message}</span>
      <button
        type="button"
        onClick={onFermer}
        aria-label="Fermer"
        className="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded text-succes transition-colors duration-150 hover:bg-succes/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
      >
        <X aria-hidden="true" size={20} strokeWidth={1.5} />
      </button>
    </div>
  )
}
