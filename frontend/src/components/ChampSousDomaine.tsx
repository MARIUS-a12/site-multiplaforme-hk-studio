import { Check, X } from 'lucide-react'
import {
  FORMAT_SOUS_DOMAINE,
  useDisponibiliteSousDomaine,
} from '../hooks/useDisponibiliteSousDomaine'

/**
 * Champ sous-domaine avec vérification de disponibilité en direct. La
 * valeur est proposée automatiquement depuis le nom par le parent
 * (PageFormulaireEtablissement) puis modifiable ici librement.
 */
export function ChampSousDomaine({
  id,
  valeur,
  onChange,
  erreur,
}: {
  id: string
  valeur: string
  onChange: (valeur: string) => void
  erreur?: string
}) {
  const { data: disponible, isFetching } = useDisponibiliteSousDomaine(valeur)
  const formatValide = FORMAT_SOUS_DOMAINE.test(valeur)

  return (
    <div>
      <label htmlFor={id} className="mb-1 block text-petit font-medium text-texte">
        Sous-domaine
        <span aria-hidden="true" className="text-danger">
          {' '}
          *
        </span>
      </label>
      <input
        id={id}
        type="text"
        value={valeur}
        onChange={(evenement) => onChange(evenement.target.value)}
        placeholder="boutique-test"
        className={`h-11 w-full rounded border bg-surface px-3 text-corps text-texte transition-colors duration-150 focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 ${
          erreur ? 'border-danger' : 'border-bordure focus:border-primaire'
        }`}
      />

      {valeur !== '' && (
        <p className="mt-1 flex items-center gap-1.5 text-petit">
          {!formatValide ? (
            <span className="text-danger">Minuscules, chiffres et tirets uniquement, sans espace.</span>
          ) : isFetching ? (
            <span className="text-texte-secondaire">Vérification…</span>
          ) : disponible === true ? (
            <span className="flex items-center gap-1 text-succes">
              <Check aria-hidden="true" size={16} strokeWidth={1.5} />
              Disponible
            </span>
          ) : disponible === false ? (
            <span className="flex items-center gap-1 text-danger">
              <X aria-hidden="true" size={16} strokeWidth={1.5} />
              Déjà utilisé ou réservé
            </span>
          ) : null}
        </p>
      )}

      {erreur && <p className="mt-1 text-petit text-danger">{erreur}</p>}
    </div>
  )
}
