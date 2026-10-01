/**
 * Appels HTTP de la vitrine publique (accueil + fiche produit) : aucune
 * authentification, voir DispositionVitrine. Les types reflètent
 * ProduitVitrineResource / ProduitVitrineDetailResource / etc. côté API —
 * volontairement plus pauvres que ceux du back-office (api/produits.ts) :
 * cette API ne renvoie jamais de quantité de stock ni de coût, seulement de
 * quoi afficher une boutique.
 */
import { client } from '../lib/client'

export type VarianteVitrine = {
  id: number
  nom: string
  prix: number
  disponible: boolean
}

export type FormatPhoto = {
  webp: string | null
  jpg: string | null
}

export type PhotoPrincipale = {
  vignette: FormatPhoto
  moyenne: FormatPhoto
}

export type PhotoVitrine = {
  id: number
  ordre: number
  est_principal: boolean
  vignette: FormatPhoto
  moyenne: FormatPhoto
  grande: FormatPhoto
}

export type CategorieReferencee = {
  id: number
  nom: string
}

export type ProduitVitrine = {
  id: number
  nom: string
  description: string | null
  prix: number
  categorie: CategorieReferencee | null
  disponible: boolean
  photo: PhotoPrincipale | null
  variantes: VarianteVitrine[]
}

export type ProduitVitrineDetail = {
  id: number
  nom: string
  description: string | null
  prix: number
  categorie: CategorieReferencee | null
  disponible: boolean
  photos: PhotoVitrine[]
  variantes: VarianteVitrine[]
}

export type CategorieVitrine = {
  id: number
  nom: string
}

export type EtablissementVitrine = {
  nom: string
  type: 'boutique' | 'restaurant'
  logo: string | null
  numero_whatsapp: string | null
  couleur_accent: string
}

export type ProduitsVitrinePagines = {
  data: ProduitVitrine[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export type ParametresListeVitrine = {
  categorie?: number
  recherche?: string
  page?: number
}

export async function recupererProduitsVitrine(
  parametres: ParametresListeVitrine,
): Promise<ProduitsVitrinePagines> {
  const { data } = await client.get<ProduitsVitrinePagines>('/api/vitrine/produits', { params: parametres })

  return data
}

export async function recupererProduitVitrine(id: number): Promise<ProduitVitrineDetail> {
  const { data } = await client.get<{ data: ProduitVitrineDetail }>(`/api/vitrine/produits/${id}`)

  return data.data
}

export async function recupererCategoriesVitrine(): Promise<CategorieVitrine[]> {
  const { data } = await client.get<{ data: CategorieVitrine[] }>('/api/vitrine/categories')

  return data.data
}

export async function recupererEtablissementVitrine(): Promise<EtablissementVitrine> {
  const { data } = await client.get<{ data: EtablissementVitrine }>('/api/vitrine/etablissement')

  return data.data
}

/**
 * Le message envoyé sur WhatsApp (nom, variante, prix, référence) est
 * composé côté serveur — voir GenerateurLienWhatsapp — jamais reconstruit
 * ici : ce serait dupliquer une logique dont la référence opaque doit
 * rester exactement celle enregistrée en base.
 */
export async function genererLienWhatsapp(produitId: number, varianteId: number | null): Promise<string> {
  const { data } = await client.post<{ url: string }>(`/api/vitrine/produits/${produitId}/lien-whatsapp`, {
    variante_id: varianteId,
  })

  return data.url
}
