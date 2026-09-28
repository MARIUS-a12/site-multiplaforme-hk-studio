import type { ModeStock } from '../api/produits'
import { Bascule } from './Bascule'
import { ChampQuantite } from './ChampQuantite'

const LIBELLE_MODE: Record<ModeStock, string> = {
  compte: 'Suivi par quantité',
  interrupteur: 'Disponibilité manuelle',
}

/**
 * Bloc stock du formulaire produit — sa forme dépend du mode de
 * l'établissement, jamais d'un choix de l'utilisateur : boutique (compte)
 * a une quantité, restaurant (interrupteur) un simple booléen disponible.
 * En modification, le mode lui-même est affiché en lecture seule avec une
 * explication : l'API le refuse s'il change, et le masquer laisserait le
 * commerçant sans réponse à "pourquoi je ne peux pas le changer ?".
 */
export function BlocStock({
  modeStock,
  estModification,
  quantiteStock,
  onQuantiteStockChange,
  erreurQuantiteStock,
  disponible,
  onDisponibleChange,
  erreurDisponible,
}: {
  modeStock: ModeStock
  estModification: boolean
  quantiteStock: number
  onQuantiteStockChange: (valeur: number) => void
  erreurQuantiteStock?: string
  disponible: boolean
  onDisponibleChange: (valeur: boolean) => void
  erreurDisponible?: string
}) {
  return (
    <div>
      <h2 className="mb-2 text-titre-section font-semibold text-texte">Stock</h2>

      {estModification && (
        <p className="mb-3 text-petit text-texte-secondaire">
          Mode de stock : <span className="font-medium text-texte">{LIBELLE_MODE[modeStock]}</span>.
          Ce réglage est fixé à la création et ne peut plus être modifié.
        </p>
      )}

      {modeStock === 'compte' ? (
        <ChampQuantite
          id="quantite_stock"
          label="Quantité en stock"
          valeur={quantiteStock}
          onChange={onQuantiteStockChange}
          erreur={erreurQuantiteStock}
        />
      ) : (
        <div id="disponible">
          <Bascule
            actif={disponible}
            onChange={onDisponibleChange}
            libelleActif="Disponible aujourd’hui"
            libelleInactif="Épuisé"
          />
          <p className="mt-2 text-petit text-texte-secondaire">
            Les plats épuisés restent visibles sur votre carte mais ne peuvent plus être commandés.
          </p>
          {erreurDisponible && <p className="mt-1 text-petit text-danger">{erreurDisponible}</p>}
        </div>
      )}
    </div>
  )
}
