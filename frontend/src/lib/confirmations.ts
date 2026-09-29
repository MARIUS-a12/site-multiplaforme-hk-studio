/**
 * Textes de confirmation partagés entre la liste et le formulaire, pour
 * qu'archiver un produit dise toujours exactement la même chose peu importe
 * l'écran d'où l'action est déclenchée.
 */
export function confirmerArchivageProduit(): boolean {
  return window.confirm(
    'Ce produit ne sera plus visible sur votre boutique, mais son historique de ventes est conservé. ' +
      'Vous pourrez le republier plus tard.\n\nArchiver ce produit ?',
  )
}

/**
 * Contrairement à l'archivage d'un produit, il n'y a ici aucun historique à
 * préserver : le fichier est réellement effacé du disque (voir
 * MediaProduitController::destroy côté API), d'où un texte qui ne cache pas
 * que c'est définitif.
 */
export function confirmerSuppressionPhoto(): boolean {
  return window.confirm('Cette photo sera définitivement supprimée.\n\nSupprimer cette photo ?')
}
