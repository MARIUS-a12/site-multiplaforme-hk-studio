import axios from 'axios'

/**
 * Transforme les erreurs de validation Laravel (422, un tableau de messages
 * par champ) en une map { champ: premier message }, pour affichage juste
 * sous le champ concerné. Renvoie un objet vide si l'erreur n'est pas une
 * 422 avec un corps de validation — dans ce cas l'appelant doit afficher un
 * message générique à la place.
 */
export function extraireErreursChamps(erreur: unknown): Record<string, string> {
  if (!axios.isAxiosError(erreur) || erreur.response?.status !== 422) {
    return {}
  }

  const erreurs = erreur.response.data?.errors as Record<string, string[]> | undefined

  if (!erreurs) {
    return {}
  }

  return Object.fromEntries(
    Object.entries(erreurs).map(([champ, messages]) => [champ, messages[0]]),
  )
}

/**
 * Fait défiler jusqu'au premier champ en erreur (dans l'ordre du
 * formulaire) et lui donne le focus quand c'est un champ natif. Chaque champ
 * du formulaire doit porter un id égal à son nom d'API pour que ça
 * fonctionne.
 */
export function allerAuPremierChampEnErreur(erreurs: Record<string, string>, ordre: string[]) {
  const premierChamp = ordre.find((champ) => erreurs[champ] !== undefined)

  if (!premierChamp) {
    return
  }

  const element = document.getElementById(premierChamp)
  element?.scrollIntoView({ behavior: 'smooth', block: 'center' })

  if (
    element instanceof HTMLInputElement ||
    element instanceof HTMLTextAreaElement ||
    element instanceof HTMLSelectElement
  ) {
    element.focus()
  }
}
