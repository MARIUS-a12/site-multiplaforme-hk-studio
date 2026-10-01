/**
 * Noir ou blanc, pour poser du texte/une icône sur la couleur d'accent
 * choisie par un établissement (voir DispositionVitrine) — jamais la
 * couleur elle-même comme fond de texte. Garantie mathématique : le
 * contraste d'une couleur contre le noir, multiplié par son contraste
 * contre le blanc, vaut toujours 21 (propriété de la formule WCAG). Si l'un
 * des deux ratios passe sous 4,5:1, l'autre dépasse donc forcément
 * 21 / 4,5 ≈ 4,67:1 — il n'existe aucune couleur pour laquelle les deux
 * échouent.
 */
function luminanceRelative(hex: string): number {
  const valeur = hex.replace('#', '')
  const [r, g, b] = [0, 2, 4].map((decalage) => parseInt(valeur.slice(decalage, decalage + 2), 16) / 255)

  const lineariser = (canal: number) =>
    canal <= 0.03928 ? canal / 12.92 : ((canal + 0.055) / 1.055) ** 2.4

  return 0.2126 * lineariser(r) + 0.7152 * lineariser(g) + 0.0722 * lineariser(b)
}

export function couleurTexteSurAccent(couleurAccent: string): '#000000' | '#ffffff' {
  const luminance = luminanceRelative(couleurAccent)
  const contrasteAvecNoir = (luminance + 0.05) / 0.05
  const contrasteAvecBlanc = 1.05 / (luminance + 0.05)

  return contrasteAvecBlanc >= contrasteAvecNoir ? '#ffffff' : '#000000'
}
