/**
 * Les trois actions d'achat de la fiche produit, dans l'ordre imposé :
 * payer maintenant, commander sur WhatsApp, ajouter au panier. Paiement et
 * panier n'existent pas encore — ils ouvrent juste un panneau qui le dit.
 * WhatsApp, lui, fonctionne réellement : le lien (avec sa référence opaque)
 * est composé côté serveur, voir api/vitrine.ts.
 *
 * Si le produit a des variantes, les trois boutons restent désactivés tant
 * qu'aucune n'est choisie — impossible de commander "le produit" sans
 * préciser laquelle.
 */
import { useState } from 'react'
import { CreditCard, MessageCircle, ShoppingCart } from 'lucide-react'
import { useMutation } from '@tanstack/react-query'
import type { VarianteVitrine } from '../api/vitrine'
import { genererLienWhatsapp } from '../api/vitrine'
import { PanneauMessage } from './PanneauMessage'

export function BoutonsAchatVitrine({
  produitId,
  disponible,
  variantes,
  varianteChoisie,
}: {
  produitId: number
  disponible: boolean
  variantes: VarianteVitrine[]
  varianteChoisie: VarianteVitrine | null
}) {
  const [panneau, setPanneau] = useState<'paiement' | 'panier' | null>(null)

  const aBesoinDuneVariante = variantes.length > 0
  const varianteIndisponible = varianteChoisie !== null && !varianteChoisie.disponible

  const peutAcheter = aBesoinDuneVariante
    ? varianteChoisie !== null && varianteChoisie.disponible
    : disponible

  const messageBlocage = aBesoinDuneVariante && varianteChoisie === null
    ? 'Choisissez une variante ci-dessus pour continuer.'
    : varianteIndisponible
      ? 'Cette variante est épuisée.'
      : !aBesoinDuneVariante && !disponible
        ? 'Ce produit est épuisé.'
        : null

  const lienWhatsapp = useMutation({
    mutationFn: () => genererLienWhatsapp(produitId, varianteChoisie?.id ?? null),
    onSuccess: (url) => {
      window.open(url, '_blank', 'noopener,noreferrer')
    },
  })

  return (
    <div>
      <div className="flex flex-col gap-3 md:flex-row">
        <button
          type="button"
          disabled={!peutAcheter}
          onClick={() => setPanneau('paiement')}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded bg-primaire text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <CreditCard aria-hidden="true" size={20} strokeWidth={1.5} />
          Payer maintenant
        </button>

        <button
          type="button"
          disabled={!peutAcheter || lienWhatsapp.isPending}
          onClick={() => lienWhatsapp.mutate()}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-primaire text-corps font-medium text-primaire transition-colors duration-150 hover:bg-primaire/10 active:bg-primaire/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <MessageCircle aria-hidden="true" size={20} strokeWidth={1.5} />
          {lienWhatsapp.isPending ? 'Préparation…' : 'Commander sur WhatsApp'}
        </button>

        <button
          type="button"
          disabled={!peutAcheter}
          onClick={() => setPanneau('panier')}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-bordure text-corps font-medium text-texte-secondaire transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <ShoppingCart aria-hidden="true" size={20} strokeWidth={1.5} />
          Ajouter au panier
        </button>
      </div>

      {messageBlocage && <p className="mt-2 text-petit text-texte-secondaire">{messageBlocage}</p>}

      {lienWhatsapp.isError && (
        <p className="mt-2 text-petit text-danger">
          Impossible de préparer le message WhatsApp. Réessayez.
        </p>
      )}

      {panneau === 'paiement' && (
        <PanneauMessage
          titre="Paiement bientôt disponible"
          message="Le paiement en ligne arrive prochainement. En attendant, commandez sur WhatsApp."
          onFermer={() => setPanneau(null)}
        />
      )}

      {panneau === 'panier' && (
        <PanneauMessage
          titre="Panier bientôt disponible"
          message="Le panier arrive prochainement. En attendant, commandez sur WhatsApp."
          onFermer={() => setPanneau(null)}
        />
      )}
    </div>
  )
}
