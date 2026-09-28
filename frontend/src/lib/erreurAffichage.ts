import axios from 'axios'

/**
 * Catégorise une erreur d'API pour décider quoi afficher : un refus (403)
 * ou une ressource introuvable (404) ne se résoudront jamais en réessayant,
 * contrairement à une panne réseau ou une erreur serveur (5xx). Le 401 n'a
 * volontairement pas de catégorie ici : il est traité globalement (voir
 * App.tsx, QueryCache.onError) en redirigeant vers la connexion, avant même
 * qu'un composant n'ait besoin d'afficher quoi que ce soit.
 */
export type TypeErreurAffichage = 'permission' | 'introuvable' | 'reseau'

export function typeErreurAffichage(erreur: unknown): TypeErreurAffichage {
  if (axios.isAxiosError(erreur) && erreur.response) {
    if (erreur.response.status === 403) {
      return 'permission'
    }

    if (erreur.response.status === 404) {
      return 'introuvable'
    }
  }

  return 'reseau'
}
