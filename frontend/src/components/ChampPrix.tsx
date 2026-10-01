import type { ChangeEvent } from 'react'

const formateur = new Intl.NumberFormat('fr-FR')

/**
 * Champ prix : n'accepte que des chiffres, les reformate avec des
 * séparateurs de milliers à chaque frappe, et affiche "FCFA" à droite.
 * La valeur transmise au parent est toujours un entier (ou null si vide) —
 * jamais la chaîne affichée.
 */
export function ChampPrix({
  id,
  label,
  valeur,
  onChange,
  erreur,
  requis,
}: {
  id: string
  label: string
  valeur: number | null
  onChange: (valeur: number | null) => void
  erreur?: string
  requis?: boolean
}) {
  const affichage = valeur === null ? '' : formateur.format(valeur)

  function gererChangement(evenement: ChangeEvent<HTMLInputElement>) {
    const chiffres = evenement.target.value.replace(/\D/g, '')
    onChange(chiffres === '' ? null : Number(chiffres))
  }

  return (
    <div>
      <label htmlFor={id} className="mb-1 block text-petit font-medium text-texte">
        {label}
        {requis && (
          <span aria-hidden="true" className="text-danger">
            {' '}
            *
          </span>
        )}
      </label>
      <div className="relative">
        <input
          id={id}
          inputMode="numeric"
          value={affichage}
          onChange={gererChangement}
          className={`h-11 w-full rounded border bg-surface pl-3 pr-14 text-corps tabular-nums text-texte transition-colors focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 ${
            erreur ? 'border-danger' : 'border-bordure focus:border-primaire'
          }`}
        />
        <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-petit text-texte-secondaire">
          FCFA
        </span>
      </div>
      {erreur && <p className="animate-entree-champ mt-1 text-petit text-danger">{erreur}</p>}
    </div>
  )
}
