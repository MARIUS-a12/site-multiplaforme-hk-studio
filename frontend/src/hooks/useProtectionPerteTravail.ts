import { useCallback, useEffect, useRef } from 'react'
import { useBlocker } from 'react-router-dom'

/**
 * Empêche de perdre un formulaire non enregistré : demande confirmation à
 * la fois pour une navigation dans l'app (lien, retour navigateur — via le
 * blocker du routeur) et pour une fermeture réelle de l'onglet
 * (beforeunload). Le commerçant tape souvent sa description sur téléphone,
 * dans un taxi — perdre ce texte n'est pas une option.
 *
 * Nécessite un routeur en mode "data" (createBrowserRouter) : useBlocker ne
 * fonctionne pas avec <BrowserRouter> seul, voir App.tsx.
 *
 * `permettreProchaineNavigation()` doit être appelé juste avant un
 * `navigate()` déclenché par un enregistrement réussi : sans ça, la
 * redirection post-sauvegarde se bloquerait elle-même. Un ref plutôt qu'un
 * state, pour être lu de façon synchrone par le routeur au moment même de
 * la navigation, sans attendre un re-rendu.
 */
export function useProtectionPerteTravail(estModifie: boolean) {
  const ignorerProchainBlocage = useRef(false)

  const doitBloquer = useCallback(() => {
    if (ignorerProchainBlocage.current) {
      ignorerProchainBlocage.current = false
      return false
    }

    return estModifie
  }, [estModifie])

  const blocker = useBlocker(doitBloquer)

  useEffect(() => {
    if (blocker.state !== 'blocked') {
      return
    }

    const confirme = window.confirm(
      'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter cette page ?',
    )

    if (confirme) {
      blocker.proceed()
    } else {
      blocker.reset()
    }
  }, [blocker])

  useEffect(() => {
    if (!estModifie) {
      return
    }

    function avantFermeture(evenement: BeforeUnloadEvent) {
      evenement.preventDefault()
      evenement.returnValue = ''
    }

    window.addEventListener('beforeunload', avantFermeture)

    return () => window.removeEventListener('beforeunload', avantFermeture)
  }, [estModifie])

  return {
    permettreProchaineNavigation: () => {
      ignorerProchainBlocage.current = true
    },
  }
}
