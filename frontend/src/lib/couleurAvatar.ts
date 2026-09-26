// Couleur et initiale des vignettes-avatar (produits sans photo,
// établissement) — consommé par CarreInitiale.tsx.
const PALETTE = [
  'bg-avatar-1',
  'bg-avatar-2',
  'bg-avatar-3',
  'bg-avatar-4',
  'bg-avatar-5',
  'bg-avatar-6',
] as const

/**
 * Hachage simple et stable (même nom → toujours la même teinte) sur une
 * palette de 6 couleurs sourdes. Pas besoin de cryptographique ici, juste
 * d'une répartition qui paraisse arbitraire à l'oeil.
 */
export function classeFondAvatar(nom: string): string {
  let somme = 0
  for (let i = 0; i < nom.length; i++) {
    somme += nom.charCodeAt(i)
  }

  return PALETTE[somme % PALETTE.length]
}

export function initiale(nom: string): string {
  return nom.trim().charAt(0).toUpperCase() || '?'
}
