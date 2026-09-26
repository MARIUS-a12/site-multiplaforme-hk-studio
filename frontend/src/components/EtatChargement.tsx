/**
 * Blocs gris à la forme du contenu à venir plutôt qu'un tourniquet centré :
 * le commerçant voit tout de suite la structure de la liste qui arrive.
 */
export function EtatChargement() {
  return (
    <div className="space-y-3" aria-busy="true" aria-label="Chargement des produits">
      {Array.from({ length: 6 }, (_, index) => (
        <div key={index} className="h-16 animate-pulse rounded border border-bordure bg-surface-alt" />
      ))}
    </div>
  )
}
