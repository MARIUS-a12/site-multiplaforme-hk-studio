/**
 * État d'erreur générique pour une liste : message clair en français +
 * bouton Réessayer. Jamais d'écran blanc, jamais de message technique brut.
 */
import { AlertTriangle } from 'lucide-react'

export function EtatErreur({ onReessayer }: { onReessayer: () => void }) {
  return (
    <div className="flex flex-col items-center gap-3 border border-bordure bg-surface px-6 py-16 text-center">
      <AlertTriangle aria-hidden="true" size={40} strokeWidth={1.5} className="text-danger" />
      <p className="max-w-sm text-corps text-texte">
        Impossible de récupérer les produits. Vérifiez votre connexion et réessayez.
      </p>
      <button
        type="button"
        onClick={onReessayer}
        className="mt-2 inline-flex h-11 cursor-pointer items-center rounded border border-bordure bg-surface px-4 text-corps font-medium text-texte transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
      >
        Réessayer
      </button>
    </div>
  )
}
