/**
 * Client HTTP central de l'app : une seule instance axios, configurée pour
 * Sanctum en mode SPA (cookies + jeton CSRF). Tous les appels API (api/*.ts)
 * passent par ici — ne pas créer d'autre instance axios ailleurs.
 */
import axios from 'axios'

const baseURL = import.meta.env.VITE_API_URL

if (!baseURL) {
  throw new Error(
    "VITE_API_URL n'est pas défini. Copiez .env.example vers .env et renseignez l'URL de l'API.",
  )
}

export const client = axios.create({
  baseURL,
  withCredentials: true,
  headers: { Accept: 'application/json' },
  // Le frontend (port 5173) et l'API (port 8000) sont deux origines
  // distinctes même sur le même sous-domaine : sans withXSRFToken, axios
  // n'attache le cookie XSRF-TOKEN à l'en-tête X-XSRF-TOKEN que pour les
  // requêtes same-origin.
  withXSRFToken: true,
})

let cookieCsrfEnCours: Promise<unknown> | null = null

function possedeCookieCsrf(): boolean {
  return document.cookie.split('; ').some((c) => c.startsWith('XSRF-TOKEN='))
}

/**
 * Sanctum (mode SPA) exige un cookie XSRF-TOKEN déjà posé avant toute
 * requête qui modifie l'état (POST/PUT/PATCH/DELETE) : sans cet appel
 * préalable à /sanctum/csrf-cookie, la requête échoue avec 419 (jeton CSRF
 * manquant), y compris la toute première tentative de connexion.
 */
function assurerCookieCsrf() {
  if (possedeCookieCsrf()) {
    return Promise.resolve()
  }

  cookieCsrfEnCours ??= client.get('/sanctum/csrf-cookie').finally(() => {
    cookieCsrfEnCours = null
  })

  return cookieCsrfEnCours
}

client.interceptors.request.use(async (config) => {
  const methode = (config.method ?? 'get').toLowerCase()

  if (methode !== 'get' && methode !== 'head') {
    await assurerCookieCsrf()
  }

  return config
})
