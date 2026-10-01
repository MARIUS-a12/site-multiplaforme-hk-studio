/**
 * Destination du lien "Paiement" du menu utilisateur (Étape 7) — l'intégration
 * de paiement elle-même est une étape à venir, non traitée ici ; cette page
 * existe pour que le lien du menu ne pointe jamais vers un écran absent.
 */
export function PagePaiement() {
  return (
    <div className="mx-auto max-w-xl">
      <h1 className="text-titre-page font-semibold text-texte">Paiement</h1>
      <p className="mt-3 text-corps text-texte-secondaire">
        La configuration des moyens de paiement arrive dans une prochaine étape.
      </p>
    </div>
  )
}
