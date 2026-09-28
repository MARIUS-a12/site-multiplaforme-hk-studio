import { Component } from 'react'
import type { ReactNode } from 'react'

type Props = { children: ReactNode }
type State = { aEchoue: boolean }

/**
 * Filet de sécurité racine : une exception React non rattrapée ailleurs ne
 * doit jamais laisser une page blanche sans explication. Doit être une
 * classe — il n'existe pas d'équivalent à base de hooks pour
 * getDerivedStateFromError/componentDidCatch.
 */
export class FrontiereErreur extends Component<Props, State> {
  state: State = { aEchoue: false }

  static getDerivedStateFromError(): State {
    return { aEchoue: true }
  }

  componentDidCatch(erreur: unknown) {
    console.error(erreur)
  }

  render() {
    if (this.state.aEchoue) {
      return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-3 px-4 text-center">
          <p className="max-w-sm text-corps text-texte">
            Une erreur inattendue est survenue. Le reste de l'application n'a pas pu s'afficher.
          </p>
          <button
            type="button"
            onClick={() => window.location.reload()}
            className="h-11 cursor-pointer rounded bg-primaire px-4 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
          >
            Recharger la page
          </button>
        </div>
      )
    }

    return this.props.children
  }
}
