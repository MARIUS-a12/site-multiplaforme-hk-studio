/**
 * Quantité en stock (mode "compte") : un champ numérique flanqué de deux
 * gros boutons − / +, assez grands pour être touchés du doigt (44px).
 */
export function ChampQuantite({
  id,
  label,
  valeur,
  onChange,
  erreur,
}: {
  id: string
  label: string
  valeur: number
  onChange: (valeur: number) => void
  erreur?: string
}) {
  const classesBouton =
    'flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded border border-bordure text-titre-section font-semibold text-texte transition-[background-color,transform] hover:bg-surface-alt active:scale-[0.97] active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1'

  return (
    <div>
      <label htmlFor={id} className="mb-1 block text-petit font-medium text-texte">
        {label}
      </label>
      <div className="flex items-center gap-2">
        <button
          type="button"
          aria-label="Diminuer la quantité"
          onClick={() => onChange(Math.max(0, valeur - 1))}
          className={classesBouton}
        >
          −
        </button>
        <input
          id={id}
          type="number"
          inputMode="numeric"
          min={0}
          value={valeur}
          onChange={(evenement) => onChange(Math.max(0, Number(evenement.target.value) || 0))}
          className={`h-11 w-full rounded border bg-surface text-center text-corps font-medium tabular-nums text-texte transition-colors focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 ${
            erreur ? 'border-danger' : 'border-bordure focus:border-primaire'
          }`}
        />
        <button
          type="button"
          aria-label="Augmenter la quantité"
          onClick={() => onChange(valeur + 1)}
          className={classesBouton}
        >
          +
        </button>
      </div>
      {erreur && <p className="animate-entree-champ mt-1 text-petit text-danger">{erreur}</p>}
    </div>
  )
}
