/**
 * Confirmation de la suppression DÉFINITIVE d'un établissement (Étape 7) :
 * une case à cocher ne suffit pas pour une action irréversible, l'utilisateur
 * doit retaper le nom exact. Même esprit que PanneauMessage (fond assombri +
 * panneau, pas de bibliothèque de modale), mais avec un champ de saisie et
 * un bouton qui ne s'active qu'une fois la saisie exacte.
 */
import { useState } from 'react'
import type { FormEvent } from 'react'
import { Bouton } from './Bouton'

export function PanneauSuppressionEtablissement({
  nomEtablissement,
  enCours,
  erreur,
  onConfirmer,
  onFermer,
}: {
  nomEtablissement: string
  enCours: boolean
  erreur: string | null
  onConfirmer: (saisie: string) => void
  onFermer: () => void
}) {
  const [enFermeture, setEnFermeture] = useState(false)
  const [saisie, setSaisie] = useState('')

  function demanderFermeture() {
    if (enCours) {
      return
    }
    setEnFermeture(true)
    setTimeout(onFermer, 150)
  }

  function soumettre(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    if (saisie === nomEtablissement) {
      onConfirmer(saisie)
    }
  }

  return (
    <div
      role="presentation"
      onClick={demanderFermeture}
      className={`fixed inset-0 z-20 flex items-end justify-center bg-texte/40 p-4 transition-opacity duration-rapide ease-disparition sm:items-center ${
        enFermeture ? 'opacity-0' : 'animate-entree-fondu'
      }`}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-label="Supprimer définitivement l'établissement"
        onClick={(evenement) => evenement.stopPropagation()}
        className={`w-full max-w-sm rounded-xl border border-bordure-forte bg-surface-haute p-4 shadow-flottant transition-[opacity,transform] duration-rapide ease-disparition sm:p-5 ${
          enFermeture ? 'translate-y-4 opacity-0' : 'animate-entree-panneau'
        }`}
      >
        <h2 className="text-titre-section font-semibold text-texte">Supprimer définitivement</h2>
        <p className="mt-2 text-corps text-texte-secondaire">
          Cette action est irréversible : domaines, produits, catégories, médias, clients, zones de livraison et
          comptes exclusivement rattachés à <strong className="font-semibold text-texte">{nomEtablissement}</strong>{' '}
          seront effacés. Rien ne pourra être récupéré.
        </p>

        <form onSubmit={soumettre} className="mt-4 space-y-3">
          <div>
            <label htmlFor="nom_confirmation" className="mb-1 block text-petit font-medium text-texte">
              Tapez « {nomEtablissement} » pour confirmer
            </label>
            <input
              id="nom_confirmation"
              type="text"
              autoComplete="off"
              value={saisie}
              onChange={(evenement) => setSaisie(evenement.target.value)}
              className="h-11 w-full rounded-md border border-bordure bg-surface px-3 text-corps text-texte transition-colors focus:border-primaire focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1"
            />
          </div>

          {erreur && (
            <p role="alert" className="animate-entree-champ text-petit text-danger">
              {erreur}
            </p>
          )}

          <div className="flex gap-3 pt-1">
            <Bouton variante="tertiaire" type="button" onClick={demanderFermeture} disabled={enCours}>
              Annuler
            </Bouton>
            <Bouton
              variante="danger"
              type="submit"
              disabled={saisie !== nomEtablissement}
              chargement={enCours}
              className="flex-1"
            >
              Supprimer définitivement
            </Bouton>
          </div>
        </form>
      </div>
    </div>
  )
}
