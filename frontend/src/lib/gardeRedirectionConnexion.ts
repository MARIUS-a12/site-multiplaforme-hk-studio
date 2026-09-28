/**
 * Garde-fou structurel contre une boucle de redirections vers /connexion.
 *
 * Post-mortem : un 401 sur /api/moi déclenchait `removeQueries(['moi'])`
 * depuis le gestionnaire d'erreur global — y compris pour la requête /moi
 * elle-même. Comme son observateur (le useQuery de RouteProtegee) restait
 * monté à cet instant, vider son cache le faisait immédiatement se
 * relancer : nouveau 401, nouvelle suppression, nouvelle relance, sans fin
 * (des dizaines de requêtes /api/moi par seconde). Plus aucune mutation de
 * cache réactive ici : uniquement une navigation impérative, et au plus une
 * seule tant que personne ne s'est reconnecté entretemps — même si un futur
 * bug fait boucler un 401 sur une autre requête, l'app ne peut plus
 * marteler ni le routeur ni le serveur.
 */
let redirectionDejaDeclenchee = false

export function declencherRedirectionConnexionUneSeuleFois(rediriger: () => void): void {
  if (redirectionDejaDeclenchee) {
    return
  }

  redirectionDejaDeclenchee = true
  rediriger()
}

/**
 * À appeler après une connexion réussie : une future expiration de session
 * doit pouvoir déclencher une nouvelle redirection.
 */
export function reinitialiserGardeRedirection(): void {
  redirectionDejaDeclenchee = false
}
