/**
 * Lecture, création et archivage des catégories de l'établissement courant.
 * Pas de renommage ici : l'écran Catégories (PageCategories) ne le propose
 * pas, cette étape ne le demande pas.
 */
import { client } from '../lib/client'

export type Categorie = {
  id: number
  nom: string
  slug: string
  description: string | null
  ordre: number
  statut: 'actif' | 'inactif'
  // Absent tant que le contrôleur ne l'a pas chargé (voir whenCounted côté
  // API) — en pratique toujours présent ici, /api/categories le charge
  // systématiquement.
  produits_count?: number
  created_at: string
  updated_at: string
}

export async function recupererCategories(): Promise<Categorie[]> {
  const { data } = await client.get<{ data: Categorie[] }>('/api/categories')

  return data.data
}

export async function creerCategorie(nom: string): Promise<Categorie> {
  const { data } = await client.post<{ data: Categorie }>('/api/categories', { nom })

  return data.data
}

/**
 * Archive (statut "inactif"), ne supprime rien : les produits de la
 * catégorie restent intacts, seulement détachés visuellement de la liste
 * des catégories actives.
 */
export async function archiverCategorie(id: number): Promise<void> {
  await client.delete(`/api/categories/${id}`)
}
