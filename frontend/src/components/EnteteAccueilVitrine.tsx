/**
 * Zone d'accueil de la vitrine (Étape 7) : nom (ou logo, déjà dans l'en-tête
 * global — ici le nom en grand reste la version la plus lisible), phrase de
 * présentation, état ouvert/fermé. Ce n'est pas une bannière décorative :
 * c'est l'identité du commerce, la première chose qu'un acheteur doit
 * comprendre avant de parcourir le catalogue. N'affiche rien si
 * l'établissement n'a ni description ni état d'ouverture connu — jamais de
 * "non renseigné".
 */
import type { EtablissementVitrine } from '../api/vitrine'

export function EnteteAccueilVitrine({ etablissement }: { etablissement: EtablissementVitrine }) {
  const { nom, description, etat_ouverture: etatOuverture } = etablissement

  return (
    <div className="space-y-2 pb-2">
      <h1 className="text-titre-page font-bold text-texte">{nom}</h1>

      {description && <p className="max-w-2xl text-corps text-texte-secondaire">{description}</p>}

      {etatOuverture && (
        <p className="flex items-center gap-1.5 text-petit font-medium">
          <span
            aria-hidden="true"
            className={`h-2 w-2 rounded-full ${etatOuverture.ouvert ? 'bg-succes' : 'bg-texte-secondaire'}`}
          />
          <span className={etatOuverture.ouvert ? 'text-succes' : 'text-texte-secondaire'}>
            {etatOuverture.libelle}
          </span>
        </p>
      )}
    </div>
  )
}
