/**
 * Tableau de routes de l'app, séparé de App.tsx (qui n'exporte plus alors
 * que le composant App lui-même, condition du fast-refresh de Vite) pour
 * que App.test.tsx puisse le réutiliser avec createMemoryRouter — tester
 * le routage sans dépendre de window.history ni du routeur singleton de
 * production.
 *
 * Deux mondes distincts, DEUX BRANCHES SŒURS, aucune n'englobant l'autre :
 * - la vitrine publique ("/", "/produit/:id", et sa propre "*" en repli),
 *   sous DispositionVitrine — aucune authentification, vue par n'importe
 *   quel visiteur ;
 * - le back-office ("/admin/*"), sous RouteProtegee — qui vérifie la
 *   session avant de rendre la page demandée — sauf "/admin/connexion",
 *   volontairement hors de ce groupe (voir plus bas pourquoi), et
 *   "/etablissements/*" (espace super-admin, protégé lui aussi).
 * "/" appartenait autrefois au back-office (liste de produits), et la
 * connexion à bare "/connexion" : les deux ont déménagé sous "/admin" pour
 * laisser "/" à la vitrine, conformément à l'Étape 6A. Chaque branche porte
 * son propre attrape-tout ("*" pour la vitrine, "/admin/*" pour le
 * back-office) : une URL inconnue reste dans le monde où elle a été tapée,
 * jamais redirigée silencieusement vers l'autre.
 */
import { Navigate } from 'react-router-dom'
import { DispositionVitrine } from './components/DispositionVitrine'
import { RouteProtegee } from './components/RouteProtegee'
import { PageAccueilVitrine } from './pages/PageAccueilVitrine'
import { PageCategories } from './pages/PageCategories'
import { PageCommander } from './pages/PageCommander'
import { PageCompte } from './pages/PageCompte'
import { PageConfirmationCommande } from './pages/PageConfirmationCommande'
import { PageConnexionUtilisateur } from './pages/PageConnexionUtilisateur'
import { PageDetailCommande } from './pages/PageDetailCommande'
import { PageFicheEtablissement } from './pages/PageFicheEtablissement'
import { PageFicheProduitVitrine } from './pages/PageFicheProduitVitrine'
import { PageFormulaireEtablissement } from './pages/PageFormulaireEtablissement'
import { PageFormulaireProduit } from './pages/PageFormulaireProduit'
import { PageIdentiteEtablissement } from './pages/PageIdentiteEtablissement'
import { PageIdentiteEtablissementSuperAdmin } from './pages/PageIdentiteEtablissementSuperAdmin'
import { PageIntrouvableAdmin } from './pages/PageIntrouvableAdmin'
import { PageIntrouvableVitrine } from './pages/PageIntrouvableVitrine'
import { PageListeCommandes } from './pages/PageListeCommandes'
import { PageListeEtablissements } from './pages/PageListeEtablissements'
import { PageListeProduits } from './pages/PageListeProduits'
import { PagePaiement } from './pages/PagePaiement'
import { PagePaiementSuperAdmin } from './pages/PagePaiementSuperAdmin'
import { PagePanier } from './pages/PagePanier'

export const routes = [
  // Vitrine publique : "/" et "/produit/:id", plus sa PROPRE 404 en repli
  // ("*") — jamais l'attrape-tout du back-office, déclaré dans une branche
  // sœur ci-dessous qui n'a aucun lien avec celle-ci.
  {
    element: <DispositionVitrine />,
    children: [
      { path: '/', element: <PageAccueilVitrine /> },
      { path: '/produit/:id', element: <PageFicheProduitVitrine /> },
      { path: '/panier', element: <PagePanier /> },
      { path: '/commander', element: <PageCommander /> },
      { path: '/commande/:numero', element: <PageConfirmationCommande /> },
      { path: '*', element: <PageIntrouvableVitrine /> },
    ],
  },
  // Connexion du back-office : publique, donc VOLONTAIREMENT hors du
  // groupe RouteProtegee ci-dessous — RouteProtegee redirige VERS cette
  // route pour une session invalide ; si elle passait elle-même par
  // RouteProtegee, ce serait une boucle de redirection.
  { path: '/admin/connexion', element: <PageConnexionUtilisateur /> },
  // Le groupe /admin (back-office) est déclaré AVANT toute règle qui
  // pourrait autrement l'intercepter : react-router classe en réalité les
  // routes par spécificité plutôt que par ordre de déclaration, mais cet
  // ordre reste le plus lisible pour qui relit ce fichier.
  {
    element: <RouteProtegee />,
    children: [
      // Espace super-admin (voir RouteProtegee, qui y confine tout
      // utilisateur sans établissement).
      { path: '/etablissements', element: <PageListeEtablissements /> },
      { path: '/etablissements/nouveau', element: <PageFormulaireEtablissement /> },
      { path: '/etablissements/:id', element: <PageFicheEtablissement /> },
      { path: '/etablissements/:id/identite', element: <PageIdentiteEtablissementSuperAdmin /> },
      { path: '/etablissements/:id/paiement', element: <PagePaiementSuperAdmin /> },
      // Espace commerçant, entièrement sous /admin.
      { path: '/admin', element: <Navigate to="/admin/produits" replace /> },
      { path: '/admin/produits', element: <PageListeProduits /> },
      { path: '/admin/produits/nouveau', element: <PageFormulaireProduit /> },
      { path: '/admin/produits/:id/modifier', element: <PageFormulaireProduit /> },
      { path: '/admin/categories', element: <PageCategories /> },
      { path: '/admin/commandes', element: <PageListeCommandes /> },
      { path: '/admin/commandes/:id', element: <PageDetailCommande /> },
      { path: '/admin/identite', element: <PageIdentiteEtablissement /> },
      // Accessible à tout utilisateur authentifié, super-admin compris —
      // voir RouteProtegee, qui laisse passer ce chemin précis pour lui.
      { path: '/admin/compte', element: <PageCompte /> },
      { path: '/admin/paiement', element: <PagePaiement /> },
      // Attrape-tout du back-office : une URL /admin/* inconnue reste dans
      // le back-office (et exige toujours la session, RouteProtegee
      // s'applique ici comme à toute route de ce groupe) — jamais
      // redirigée vers la vitrine.
      { path: '/admin/*', element: <PageIntrouvableAdmin /> },
    ],
  },
]
