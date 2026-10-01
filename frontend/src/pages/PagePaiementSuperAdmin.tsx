/**
 * Écran /etablissements/:id/paiement — chemin super-admin du formulaire de
 * paiement unique (voir FormulairePaiement), sur n'importe quel
 * établissement, pour pouvoir dépanner un commerçant.
 */
import { useMemo } from 'react'
import { useParams } from 'react-router-dom'
import { servicePaiementSuperAdmin } from '../api/paiement'
import { BoutonRetour } from '../components/BoutonRetour'
import { FormulairePaiement } from '../components/FormulairePaiement'

export function PagePaiementSuperAdmin() {
  const { id } = useParams()
  const etablissementId = Number(id)
  const service = useMemo(() => servicePaiementSuperAdmin(etablissementId), [etablissementId])

  return (
    <div className="mx-auto max-w-2xl pb-4">
      <div className="mb-4 flex items-center gap-2">
        <BoutonRetour vers={`/etablissements/${etablissementId}`} />
        <h1 className="text-titre-page font-bold text-texte">Paiement de l'établissement</h1>
      </div>

      <FormulairePaiement service={service} />
    </div>
  )
}
