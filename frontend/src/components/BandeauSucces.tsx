import { useEffect } from 'react'
import { X } from 'lucide-react'

/**
 * Bandeau de confirmation après une action réussie (création, modification,
 * archivage), affiché en haut de la liste de produits. Se ferme au clic, ou
 * tout seul après 4 secondes — une confirmation n'a pas besoin d'attendre
 * qu'on la chasse ; une erreur, elle, ne doit jamais disparaître seule, d'où
 * ce délai posé UNIQUEMENT ici et jamais sur un message d'erreur. La page
 * qui le montre reste responsable de vider l'état de navigation qui le
 * porte, pour qu'il ne réapparaisse pas au rechargement.
 */
export function BandeauSucces({ message, onFermer }: { message: string; onFermer: () => void }) {
  useEffect(() => {
    const identifiant = setTimeout(onFermer, 4000)

    return () => clearTimeout(identifiant)
  }, [onFermer])

  return (
    <div
      role="status"
      className="animate-entree-haut flex items-center justify-between gap-3 border border-succes bg-succes/10 px-4 py-3 text-corps text-succes"
    >
      <span>{message}</span>
      <button
        type="button"
        onClick={onFermer}
        aria-label="Fermer"
        className="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded text-succes transition-[background-color,transform] duration-rapide ease-apparition hover:bg-succes/10 active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
      >
        <X aria-hidden="true" size={20} strokeWidth={1.5} />
      </button>
    </div>
  )
}
