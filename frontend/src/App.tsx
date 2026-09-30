/**
 * Racine de l'app : fournisseur react-query (cache des appels API) +
 * routeur. L'arbre de routes lui-même vit dans routes.tsx (voir sa
 * docblock pour le partage vitrine publique / back-office protégé) — ce
 * fichier ne construit que le routeur et les fournisseurs qui l'entourent.
 *
 * Routeur en mode "data" (createBrowserRouter), pas <BrowserRouter> seul :
 * la protection contre la perte de travail du formulaire produit
 * (useProtectionPerteTravail) a besoin de useBlocker, qui n'existe qu'en
 * mode data.
 */
import { QueryCache, QueryClient, QueryClientProvider } from '@tanstack/react-query'
import axios from 'axios'
import { createBrowserRouter, RouterProvider } from 'react-router-dom'
import { CLE_MOI } from './hooks/useMoi'
import { declencherRedirectionConnexionUneSeuleFois } from './lib/gardeRedirectionConnexion'
import { routes } from './routes'

/**
 * Un refus (403), une ressource introuvable (404) ou des identifiants
 * invalides (422) ne se résoudront jamais en réessayant la même requête :
 * ne réessayer que les pannes réseau (pas de réponse du tout) et les
 * erreurs serveur (5xx), avec un délai croissant, plafonnées à 2 essais —
 * sans ce garde-fou, une seule 403 déclenche par défaut 3 nouvelles
 * tentatives par requête react-query, ce qui explose vite en dizaines
 * d'appels sur une page qui charge plusieurs ressources à la fois.
 */
function doitReessayer(nombreEchecs: number, erreur: unknown): boolean {
  if (nombreEchecs >= 2) {
    return false
  }

  if (axios.isAxiosError(erreur) && erreur.response) {
    return erreur.response.status >= 500
  }

  // Pas de réponse du tout : panne réseau, celle-là vaut la peine d'être
  // retentée.
  return true
}

function delaiReessai(nombreEchecs: number): number {
  return Math.min(1000 * 2 ** nombreEchecs, 30000)
}

const router = createBrowserRouter(routes)

/**
 * Un 401 sur une requête AUTRE que /api/moi (celle-là, RouteProtegee la
 * gère déjà directement via son propre isError — voir sa docblock) signifie
 * que la session a expiré en cours d'utilisation : on redirige directement
 * vers la connexion via le routeur, une seule fois (voir
 * gardeRedirectionConnexion.ts pour le pourquoi). Aucune mutation du cache
 * react-query ici, volontairement.
 */
const queryCache = new QueryCache({
  onError: (erreur, query) => {
    if (query.queryKey[0] === CLE_MOI[0]) {
      return
    }

    if (axios.isAxiosError(erreur) && erreur.response?.status === 401) {
      declencherRedirectionConnexionUneSeuleFois(() => router.navigate('/admin/connexion', { replace: true }))
    }
  },
})

const queryClient = new QueryClient({
  queryCache,
  defaultOptions: {
    queries: {
      retry: doitReessayer,
      retryDelay: delaiReessai,
    },
  },
})

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
    </QueryClientProvider>
  )
}

export default App
