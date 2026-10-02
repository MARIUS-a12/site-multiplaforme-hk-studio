/**
 * Badge coloré du statut d'une commande (Étape 9), cohérent avec la palette
 * du back-office : neutre en attente, bleu confirmée, violet prête, vert
 * livrée (succès, terminal), rouge annulée. Un aplat clair avec son texte de
 * la même teinte — jamais de texte blanc ici, ce sont des badges, pas des
 * boutons.
 */
import type { StatutCommande } from '../api/commandes'

const LIBELLES: Record<StatutCommande, string> = {
  attente_paiement: 'En attente',
  payee: 'Confirmée',
  prete: 'Prête',
  livree: 'Livrée',
  expiree: 'Expirée',
  annulee: 'Annulée',
}

const CLASSES: Record<StatutCommande, string> = {
  attente_paiement: 'bg-texte-secondaire/10 text-texte-secondaire',
  payee: 'bg-bleu-info/10 text-bleu-info',
  prete: 'bg-violet/10 text-violet',
  livree: 'bg-primaire/10 text-primaire',
  expiree: 'bg-texte-secondaire/10 text-texte-secondaire',
  annulee: 'bg-danger/10 text-danger',
}

export function BadgeStatutCommande({ statut }: { statut: StatutCommande }) {
  return (
    <span
      className={`inline-flex items-center rounded-md px-2.5 py-1 text-petit font-medium ${CLASSES[statut]}`}
    >
      {LIBELLES[statut]}
    </span>
  )
}
