/**
 * Groupe de boutons exclusifs (un choix parmi N), visuellement une seule
 * pilule divisée. Utilisé pour le filtre de statut (BarreFiltres) et pour
 * le champ Statut du formulaire produit — un seul composant, pour que les
 * deux se comportent et se ressemblent exactement de la même façon.
 */
export function GroupeSegmente<T extends string>({
  options,
  valeur,
  onChange,
  id,
}: {
  options: { valeur: T; libelle: string }[]
  valeur: T
  onChange: (valeur: T) => void
  id?: string
}) {
  return (
    <div id={id} className="inline-flex overflow-hidden rounded border border-bordure">
      {options.map((option, index) => (
        <button
          key={option.valeur}
          type="button"
          onClick={() => onChange(option.valeur)}
          aria-pressed={valeur === option.valeur}
          className={`h-11 flex-1 cursor-pointer px-3 text-corps font-medium transition-colors duration-150 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 ${
            index > 0 ? 'border-l border-bordure' : ''
          } ${
            valeur === option.valeur
              ? 'bg-primaire text-surface'
              : 'bg-surface text-texte hover:bg-surface-alt'
          }`}
        >
          {option.libelle}
        </button>
      ))}
    </div>
  )
}
