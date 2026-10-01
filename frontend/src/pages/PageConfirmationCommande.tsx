/**
 * Écran public "/commande/:numero?jeton=..." — confirmation après validation
 * de la commande. Le numéro seul se devine (CMD-000847) : sans le jeton
 * d'accès généré à la création, le serveur renvoie 404, affiché ici
 * identiquement à un numéro inexistant (voir VitrineCommandeController côté
 * API) — jamais un indice qu'une commande existe sans le bon jeton.
 */
import { MessageCircle } from 'lucide-react'
import { useQuery } from '@tanstack/react-query'
import { useParams, useSearchParams } from 'react-router-dom'
import { EtatErreur } from '../components/EtatErreur'
import { useEtablissementVitrine } from '../hooks/useEtablissementVitrine'
import { recupererCommandeVitrine } from '../api/vitrine'
import { formaterMontant } from '../lib/formatage'

const LIBELLES_STATUT: Record<string, string> = {
  attente_paiement: 'En attente de paiement',
  payee: 'Payée',
  expiree: 'Expirée',
  annulee: 'Annulée',
}

export function PageConfirmationCommande() {
  const { numero } = useParams()
  const [parametres] = useSearchParams()
  const jeton = parametres.get('jeton') ?? ''

  const { data: etablissement } = useEtablissementVitrine()
  const { data: commande, isPending, isError, error, refetch } = useQuery({
    queryKey: ['commande-vitrine', numero, jeton],
    queryFn: () => recupererCommandeVitrine(numero as string, jeton),
    enabled: Boolean(numero),
    retry: false,
  })

  if (isPending) {
    return (
      <div className="mx-auto max-w-xl space-y-3">
        <div className="h-7 w-2/3 animate-pulse rounded bg-surface-alt" />
        <div className="h-32 w-full animate-pulse rounded bg-surface-alt" />
      </div>
    )
  }

  if (isError || !commande) {
    return (
      <div className="mx-auto max-w-xl">
        <EtatErreur erreur={error} onReessayer={() => refetch()} />
      </div>
    )
  }

  const lienWhatsapp = etablissement?.numero_whatsapp
    ? `https://wa.me/${etablissement.numero_whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
        `Bonjour, je voudrais suivre ma commande ${commande.numero}.`,
      )}`
    : null

  return (
    <div className="mx-auto max-w-xl space-y-6 pb-4">
      <div>
        <p className="text-petit text-texte-secondaire">Commande</p>
        <h1 className="tabular-nums text-titre-page font-semibold text-texte">{commande.numero}</h1>
        <p className="mt-1 text-corps text-texte-secondaire">
          Statut : <span className="font-medium text-texte">{LIBELLES_STATUT[commande.statut] ?? commande.statut}</span>
        </p>
      </div>

      <p className="border border-bordure bg-surface p-3 text-corps text-texte-secondaire">
        Le paiement en ligne n'est pas encore disponible. Le commerçant va vous recontacter pour organiser le
        règlement.
      </p>

      <div className="border border-bordure bg-surface p-4">
        <h2 className="mb-3 text-titre-section font-semibold text-texte">Articles</h2>
        <ul className="space-y-1.5 text-corps text-texte">
          {commande.lignes.map((ligne, index) => (
            <li key={index} className="flex justify-between gap-2">
              <span className="truncate text-texte-secondaire">
                {ligne.quantite} × {ligne.nom}
              </span>
              <span className="shrink-0 tabular-nums">{formaterMontant(ligne.total)}</span>
            </li>
          ))}
        </ul>
        <div className="mt-3 space-y-1 border-t border-bordure pt-3 text-corps">
          <div className="flex justify-between text-texte-secondaire">
            <span>Sous-total</span>
            <span className="tabular-nums">{formaterMontant(commande.sous_total)}</span>
          </div>
          <div className="flex justify-between text-texte-secondaire">
            <span>Livraison{commande.zone_livraison ? ` (${commande.zone_livraison.nom})` : ''}</span>
            <span className="tabular-nums">{formaterMontant(commande.frais_livraison)}</span>
          </div>
          <div className="flex justify-between text-titre-section font-semibold text-texte">
            <span>Total</span>
            <span className="tabular-nums">{formaterMontant(commande.total)}</span>
          </div>
        </div>
      </div>

      {lienWhatsapp && (
        <a
          href={lienWhatsapp}
          target="_blank"
          rel="noopener noreferrer"
          className="flex h-11 w-full cursor-pointer items-center justify-center gap-1.5 rounded border border-texte text-corps font-medium text-texte transition-[background-color,transform] hover:bg-surface-alt active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1"
        >
          <MessageCircle aria-hidden="true" size={20} strokeWidth={1.5} />
          Suivre ma commande sur WhatsApp
        </a>
      )}
    </div>
  )
}
