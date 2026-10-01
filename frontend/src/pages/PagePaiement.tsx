/**
 * Écran /admin/paiement — chemin commerçant du formulaire de paiement
 * unique (voir FormulairePaiement), sur son propre établissement.
 * N'apparaît dans le menu utilisateur que pour qui a gerer_parametres (voir
 * MenuUtilisateur).
 */
import { BoutonRetour } from '../components/BoutonRetour'
import { FormulairePaiement } from '../components/FormulairePaiement'
import { servicePaiementCommercant } from '../api/paiement'

const service = servicePaiementCommercant()

export function PagePaiement() {
  return (
    <div className="mx-auto max-w-2xl">
      <div className="mb-4 flex items-center gap-2">
        <BoutonRetour vers="/admin/produits" />
        <h1 className="text-titre-page font-bold text-texte">Paiement</h1>
      </div>

      <FormulairePaiement service={service} />
    </div>
  )
}
