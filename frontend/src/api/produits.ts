/**
 * Types et appel HTTP de la liste paginée des produits. Les types (Produit,
 * StatutProduit, ModeStock) reflètent la forme de ProduitResource côté API
 * Laravel : si l'un d'eux change côté backend, c'est ici qu'il faut le
 * répercuter.
 */
import { client } from '../lib/client'

export type StatutProduit = 'brouillon' | 'publie' | 'archive'
export type ModeStock = 'compte' | 'interrupteur'

export type Produit = {
  id: number
  categorie_id: number | null
  nom: string
  slug: string
  description: string | null
  reference: string | null
  prix: number
  prix_barre: number | null
  mode_stock: ModeStock
  quantite_stock: number
  quantite_reservee: number
  disponible: boolean
  statut: StatutProduit
  publie_le: string | null
  created_at: string
  updated_at: string
}

export type ColonneTri = 'nom' | 'date'
export type Direction = 'asc' | 'desc'

export type ParametresListeProduits = {
  recherche?: string
  statut?: StatutProduit
  tri?: ColonneTri
  direction?: Direction
  page?: number
  par_page?: number
}

export type ProduitsPagines = {
  data: Produit[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export async function recupererProduits(
  parametres: ParametresListeProduits,
): Promise<ProduitsPagines> {
  const { data } = await client.get<ProduitsPagines>('/api/produits', {
    params: parametres,
  })

  return data
}
