/**
 * Écran /admin/identite — chemin commerçant du formulaire d'identité unique
 * (voir FormulaireIdentiteEtablissement), sur son propre établissement.
 * N'apparaît dans la navigation que pour qui a gerer_parametres (voir
 * CoquilleApplication).
 */
import { BoutonRetour } from '../components/BoutonRetour'
import { FormulaireIdentiteEtablissement } from '../components/FormulaireIdentiteEtablissement'
import { serviceIdentiteCommercant } from '../api/identite'

const service = serviceIdentiteCommercant()

export function PageIdentiteEtablissement() {
  return (
    <div className="mx-auto max-w-2xl">
      <div className="mb-4 flex items-center gap-2">
        <BoutonRetour vers="/admin/produits" />
        <h1 className="text-titre-page font-semibold text-texte">Identité de la boutique</h1>
      </div>

      <FormulaireIdentiteEtablissement service={service} />
    </div>
  )
}
