/**
 * Décalage d'entrée d'une carte de liste : 30ms entre chaque carte, pour les
 * 6 premières seulement — au-delà, tout le reste apparaît d'un bloc, au même
 * délai que la 6e (un décalage individuel sur toute une page de 24 cartes
 * donnerait une impression de lenteur, pas d'élégance). Retourne un style
 * prêt à poser sur l'élément qui porte animate-entree-carte (voir index.css).
 */
export function styleEntreeListe(index: number): { animationDelay: string } {
  return { animationDelay: `${Math.min(index, 5) * 30}ms` }
}
