/**
 * Bandeau en haut de la liste de produits : combien sont publiés, en
 * rupture de stock, ou encore en brouillon. Le chiffre de rupture est mis
 * en rouge dès qu'il dépasse zéro — c'est ce que le commerçant doit pouvoir
 * repérer en un coup d'oeil.
 */
import { useStatistiquesProduits } from '../hooks/useStatistiquesProduits'

export function BandeStatistiques() {
  const { publies, brouillons, ruptures, isPending } = useStatistiquesProduits()

  return (
    <div className="grid grid-cols-2 border border-bordure sm:grid-cols-3">
      <Indicateur
        libelle="Produits publiés"
        valeur={publies}
        chargement={isPending}
        className="border-r border-bordure"
      />
      <Indicateur
        libelle="En rupture"
        valeur={ruptures}
        chargement={isPending}
        accent={ruptures > 0 ? 'danger' : undefined}
        className="sm:border-r sm:border-bordure"
      />
      <Indicateur
        libelle="Brouillons"
        valeur={brouillons}
        chargement={isPending}
        className="col-span-2 border-t border-bordure sm:col-span-1 sm:border-t-0"
      />
    </div>
  )
}

function Indicateur({
  libelle,
  valeur,
  chargement,
  accent,
  className,
}: {
  libelle: string
  valeur: number
  chargement: boolean
  accent?: 'danger'
  className: string
}) {
  return (
    <div className={`px-4 py-3 ${className}`}>
      <div
        className={`text-titre-page font-semibold tabular-nums ${accent === 'danger' ? 'text-danger' : 'text-texte'}`}
      >
        {chargement ? '—' : valeur}
      </div>
      <div className="text-petit text-texte-secondaire">{libelle}</div>
    </div>
  )
}
