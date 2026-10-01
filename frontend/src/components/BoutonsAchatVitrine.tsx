/**
 * Les trois actions d'achat de la fiche produit, dans l'ordre imposé :
 * payer maintenant, commander sur WhatsApp, ajouter au panier. Paiement
 * n'existe pas encore — il ouvre juste un panneau qui le dit (voir Étape 6C).
 * WhatsApp et le panier, eux, fonctionnent réellement : le lien WhatsApp
 * (avec sa référence opaque) est composé côté serveur (voir api/vitrine.ts),
 * le panier vit dans localStorage (voir lib/panier.ts) et confirme l'ajout
 * sans quitter la page — jamais de navigation, jamais de rechargement.
 *
 * Si le produit a des variantes, les trois boutons restent désactivés tant
 * qu'aucune n'est choisie — impossible de commander "le produit" sans
 * préciser laquelle.
 */
import { useEffect, useState } from 'react'
import { Check, CreditCard, MessageCircle, ShoppingCart } from 'lucide-react'
import { useMutation } from '@tanstack/react-query'
import type { VarianteVitrine } from '../api/vitrine'
import { genererLienWhatsapp } from '../api/vitrine'
import { usePanier } from '../hooks/usePanier'
import { EtatBouton } from './EtatBouton'
import { PanneauMessage } from './PanneauMessage'

export function BoutonsAchatVitrine({
  produitId,
  prix,
  disponible,
  variantes,
  varianteChoisie,
}: {
  produitId: number
  prix: number
  disponible: boolean
  variantes: VarianteVitrine[]
  varianteChoisie: VarianteVitrine | null
}) {
  const { ajouter } = usePanier()
  const [panneau, setPanneau] = useState<'paiement' | null>(null)
  const [ajoute, setAjoute] = useState(false)

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

  // Confirmation visuelle temporaire sur le bouton lui-même — jamais une
  // navigation, jamais un rechargement (voir docblock). 2s : le temps de la
  // remarquer sans ralentir qui enchaîne plusieurs ajouts.
  useEffect(() => {
    if (!ajoute) {
      return
    }

    const minuteur = setTimeout(() => setAjoute(false), 2000)
    return () => clearTimeout(minuteur)
  }, [ajoute])

  function ajouterAuPanier() {
    ajouter(produitId, varianteChoisie?.id ?? null, 1, varianteChoisie?.prix ?? prix)
    setAjoute(true)
  }

  return (
    <div>
      <div className="flex flex-col gap-3 md:flex-row">
        <button
          type="button"
          disabled={!peutAcheter}
          onClick={() => setPanneau('paiement')}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded bg-accent text-corps font-medium text-accent-texte transition-[opacity,transform] hover:opacity-90 active:scale-[0.97] active:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100"
        >
          <CreditCard aria-hidden="true" size={20} strokeWidth={1.5} />
          Payer maintenant
        </button>

        <button
          type="button"
          disabled={!peutAcheter || lienWhatsapp.isPending}
          onClick={() => lienWhatsapp.mutate()}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-texte text-corps font-medium text-texte transition-[background-color,transform] hover:bg-surface-alt active:scale-[0.97] active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100"
        >
          <EtatBouton chargement={lienWhatsapp.isPending}>
            <MessageCircle aria-hidden="true" size={20} strokeWidth={1.5} />
            Commander sur WhatsApp
          </EtatBouton>
        </button>

        <button
          type="button"
          disabled={!peutAcheter}
          onClick={ajouterAuPanier}
          className="flex h-11 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded border border-bordure text-corps font-medium text-texte-secondaire transition-[background-color,transform] hover:bg-surface-alt active:scale-[0.97] active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100"
        >
          {ajoute ? (
            <>
              <Check aria-hidden="true" size={20} strokeWidth={1.5} />
              Ajouté au panier
            </>
          ) : (
            <>
              <ShoppingCart aria-hidden="true" size={20} strokeWidth={1.5} />
              Ajouter au panier
            </>
          )}
        </button>
      </div>

      {messageBlocage && (
        <p className="animate-entree-carte mt-2 text-petit text-texte-secondaire">{messageBlocage}</p>
      )}

      {lienWhatsapp.isError && (
        <p className="animate-entree-carte mt-2 text-petit text-danger">
          Impossible de préparer le message WhatsApp. Réessayez.
        </p>
      )}

      {panneau === 'paiement' && (
        <PanneauMessage
          titre="Paiement bientôt disponible"
          message="Le paiement en ligne arrive prochainement. En attendant, commandez sur WhatsApp ou ajoutez au panier."
          onFermer={() => setPanneau(null)}
        />
      )}
    </div>
  )
}
