/**
 * Racine de l'app : fournisseur react-query (cache des appels API) +
 * routeur. Une seule route publique (/connexion) ; tout le reste passe par
 * RouteProtegee, qui vérifie la session avant de rendre la page demandée.
 */
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { RouteProtegee } from './components/RouteProtegee'
import { PageConnexionUtilisateur } from './pages/PageConnexionUtilisateur'
import { PageListeProduits } from './pages/PageListeProduits'

const queryClient = new QueryClient()

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/connexion" element={<PageConnexionUtilisateur />} />
          <Route element={<RouteProtegee />}>
            <Route path="/" element={<PageListeProduits />} />
          </Route>
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}

export default App
