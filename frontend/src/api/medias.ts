/**
 * Photos d'un produit : envoi, suppression et réordonnancement. Chaque
 * variante (vignette/moyenne/grande) porte une URL webp et une URL jpg (le
 * repli) — voir MediaResource côté API pour la forme exacte.
 */
import { client } from '../lib/client'

export type VarianteMedia = {
  webp: string | null
  jpg: string | null
}

export type Media = {
  id: number
  ordre: number
  est_principal: boolean
  vignette: VarianteMedia
  moyenne: VarianteMedia
  grande: VarianteMedia
}

export async function envoyerMediaProduit(
  produitId: number,
  fichier: File,
  onProgression?: (pourcentage: number) => void,
): Promise<Media> {
  const formulaire = new FormData()
  formulaire.append('photo', fichier)

  const { data } = await client.post<{ data: Media }>(`/api/produits/${produitId}/medias`, formulaire, {
    onUploadProgress: (evenement) => {
      if (onProgression && evenement.total) {
        onProgression(Math.round((evenement.loaded / evenement.total) * 100))
      }
    },
  })

  return data.data
}

export async function supprimerMediaProduit(produitId: number, mediaId: number): Promise<void> {
  await client.delete(`/api/produits/${produitId}/medias/${mediaId}`)
}

export async function reordonnerMediasProduit(produitId: number, ordre: number[]): Promise<Media[]> {
  const { data } = await client.put<{ data: Media[] }>(`/api/produits/${produitId}/medias/ordre`, { ordre })

  return data.data
}
