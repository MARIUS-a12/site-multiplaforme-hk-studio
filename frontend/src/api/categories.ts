/**
 * Lecture des catégories de l'établissement courant. Pas de création ni de
 * modification ici : pour l'instant, ça ne sert qu'à afficher le nom de la
 * catégorie d'un produit dans la liste (voir useCategories).
 */
import { client } from '../lib/client'

export type Categorie = {
  id: number
  nom: string
  slug: string
  description: string | null
  ordre: number
  statut: 'actif' | 'inactif'
  created_at: string
  updated_at: string
}

export async function recupererCategories(): Promise<Categorie[]> {
  const { data } = await client.get<{ data: Categorie[] }>('/api/categories')

  return data.data
}
