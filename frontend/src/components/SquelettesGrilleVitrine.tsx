/**
 * Squelettes de la grille publique pendant le chargement — jamais un
 * tourniquet plein écran (voir la contrainte de design de l'Étape 6A).
 * Même grille responsive que la vraie liste, pour ne pas sauter de mise en
 * page à l'arrivée des données.
 */
export function SquelettesGrilleVitrine() {
  return (
    <div
      aria-busy="true"
      aria-label="Chargement des produits"
      className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      {Array.from({ length: 8 }, (_, index) => (
        <div key={index} className="overflow-hidden rounded border border-bordure">
          <div className="aspect-square w-full animate-pulse bg-surface-alt" />
          <div className="space-y-2 p-3">
            <div className="h-4 w-3/4 animate-pulse rounded bg-surface-alt" />
            <div className="h-4 w-1/3 animate-pulse rounded bg-surface-alt" />
          </div>
        </div>
      ))}
    </div>
  )
}
