// Formatage des montants en FCFA pour l'affichage (jamais un nombre brut
// du type "25000" montré au commerçant).
const formateurMontant = new Intl.NumberFormat('fr-FR')

export function formaterMontant(montant: number): string {
  return `${formateurMontant.format(montant)} FCFA`
}
