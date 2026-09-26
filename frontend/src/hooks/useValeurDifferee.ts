import { useEffect, useState } from 'react'

/**
 * Retarde la propagation d'une valeur (recherche texte) pour ne pas
 * déclencher un appel API à chaque frappe.
 */
export function useValeurDifferee<T>(valeur: T, delaiMs = 300): T {
  const [valeurDifferee, setValeurDifferee] = useState(valeur)

  useEffect(() => {
    const identifiant = setTimeout(() => setValeurDifferee(valeur), delaiMs)

    return () => clearTimeout(identifiant)
  }, [valeur, delaiMs])

  return valeurDifferee
}
