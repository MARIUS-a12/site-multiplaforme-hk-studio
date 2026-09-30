/**
 * Petit panneau modal générique pour une fonctionnalité pas encore
 * disponible (paiement, panier — voir les boutons de PageFicheProduitVitrine).
 * Volontairement minimal : pas de bibliothèque de modale, juste un fond
 * assombri et une carte, comme le reste du projet gère ses confirmations
 * (voir lib/confirmations.ts pour l'équivalent côté back-office).
 */
export function PanneauMessage({
  titre,
  message,
  onFermer,
}: {
  titre: string
  message: string
  onFermer: () => void
}) {
  return (
    <div
      role="presentation"
      onClick={onFermer}
      className="fixed inset-0 z-20 flex items-end justify-center bg-texte/40 p-4 sm:items-center"
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-label={titre}
        onClick={(evenement) => evenement.stopPropagation()}
        className="w-full max-w-sm rounded border border-bordure bg-surface p-4"
      >
        <h2 className="text-titre-section font-semibold text-texte">{titre}</h2>
        <p className="mt-2 text-corps text-texte-secondaire">{message}</p>
        <button
          type="button"
          onClick={onFermer}
          className="mt-4 h-11 w-full cursor-pointer rounded bg-primaire text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
        >
          Fermer
        </button>
      </div>
    </div>
  )
}
