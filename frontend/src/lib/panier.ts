/**
 * Panier de la vitrine : localStorage, une clé par hôte (chaque établissement
 * a son propre sous-domaine, donc sa propre origine — le panier de chez-awa
 * n'est déjà, nativement, pas visible sur maquis-du-port ; la clé reprend
 * quand même l'hôte explicitement, en garde-fou).
 *
 * Contenu d'une ligne : identifiant produit, identifiant variante, quantité
 * — rien d'autre n'est envoyé au serveur à la commande. "prixVu" est à part :
 * un simple repère d'affichage (le dernier prix montré à l'utilisateur pour
 * cette ligne), qui permet à /panier/verifier de signaler un changement de
 * prix. Il n'est jamais envoyé à la création de commande — voir
 * api/vitrine.ts, dont le payload de commande ne porte que des identifiants
 * et des quantités.
 */
export type LignePanier = {
  produitId: number
  varianteId: number | null
  quantite: number
  prixVu: number | null
}

function cle(): string {
  return `panier:${window.location.host}`
}

let dernierBrut: string | null | undefined
let derniereLecture: LignePanier[] = []

export function lireLignesPanier(): LignePanier[] {
  let brut: string | null

  try {
    brut = window.localStorage.getItem(cle())
  } catch {
    return []
  }

  if (brut === dernierBrut) {
    return derniereLecture
  }

  let lignes: LignePanier[]

  try {
    const valeur: unknown = brut ? JSON.parse(brut) : []
    lignes = Array.isArray(valeur) ? valeur : []
  } catch {
    lignes = []
  }

  dernierBrut = brut
  derniereLecture = lignes

  return lignes
}

function ecrireLignesPanier(lignes: LignePanier[]): void {
  try {
    window.localStorage.setItem(cle(), JSON.stringify(lignes))
  } catch {
    return
  }

  // Notifie les composants abonnés (voir usePanier) : localStorage ne
  // déclenche l'évènement "storage" natif que dans les AUTRES onglets, jamais
  // dans celui qui vient d'écrire.
  window.dispatchEvent(new Event('panier:change'))
}

function memeLigne(a: { produitId: number; varianteId: number | null }, b: { produitId: number; varianteId: number | null }): boolean {
  return a.produitId === b.produitId && a.varianteId === b.varianteId
}

export function ajouterLignePanier(produitId: number, varianteId: number | null, quantite: number, prixVu: number): void {
  const lignes = lireLignesPanier()
  const cible = { produitId, varianteId }
  const existante = lignes.find((ligne) => memeLigne(ligne, cible))

  const nouvelles = existante
    ? lignes.map((ligne) =>
        memeLigne(ligne, cible) ? { ...ligne, quantite: ligne.quantite + quantite, prixVu } : ligne,
      )
    : [...lignes, { produitId, varianteId, quantite, prixVu }]

  ecrireLignesPanier(nouvelles)
}

export function modifierQuantitePanier(produitId: number, varianteId: number | null, quantite: number): void {
  const cible = { produitId, varianteId }
  ecrireLignesPanier(
    lireLignesPanier().map((ligne) => (memeLigne(ligne, cible) ? { ...ligne, quantite } : ligne)),
  )
}

export function retirerLignePanier(produitId: number, varianteId: number | null): void {
  const cible = { produitId, varianteId }
  ecrireLignesPanier(lireLignesPanier().filter((ligne) => !memeLigne(ligne, cible)))
}

export function definirPrixVuPanier(produitId: number, varianteId: number | null, prixVu: number): void {
  const cible = { produitId, varianteId }
  ecrireLignesPanier(
    lireLignesPanier().map((ligne) => (memeLigne(ligne, cible) ? { ...ligne, prixVu } : ligne)),
  )
}

export function viderPanier(): void {
  ecrireLignesPanier([])
}
