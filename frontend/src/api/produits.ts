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

export async function recupererProduit(id: number): Promise<Produit> {
  const { data } = await client.get<{ data: Produit }>(`/api/produits/${id}`)

  return data.data
}

/**
 * Corps envoyé à la création/modification. Ni slug (généré côté API), ni
 * mode_stock (déduit à la création, verrouillé en modification) : ces deux
 * champs n'existent pas dans le formulaire, voir PageFormulaireProduit.
 */
export type ProduitPayload = {
  nom: string
  categorie_id: number | null
  prix: number
  prix_barre: number | null
  description: string | null
  reference: string | null
  quantite_stock?: number
  disponible?: boolean
  // Absent quand on modifie un produit déjà archivé : ce formulaire ne
  // propose pas de le republier (une action à part, prévue plus tard), et
  // envoyer "brouillon" par défaut le désarchiverait sans que le
  // commerçant l'ait demandé.
  statut?: 'brouillon' | 'publie'
}

export async function creerProduit(payload: ProduitPayload): Promise<Produit> {
  const { data } = await client.post<{ data: Produit }>('/api/produits', payload)

  return data.data
}

export async function modifierProduit(id: number, payload: ProduitPayload): Promise<Produit> {
  const { data } = await client.put<{ data: Produit }>(`/api/produits/${id}`, payload)

  return data.data
}

/**
 * "Supprimer" n'existe pas dans cette API : l'appel archive (statut passe à
 * "archive"), l'historique de ventes du produit est conservé.
 */
export async function archiverProduit(id: number): Promise<void> {
  await client.delete(`/api/produits/${id}`)
}
