/**
 * Indicateur de force du mot de passe EN MOTS CLAIRS ("Faible", "Moyen",
 * "Solide"), pas une barre colorée muette qui ne dit rien à qui ne connaît
 * pas déjà le code couleur. La couleur du texte ne fait que renforcer le mot
 * — jamais le seul porteur du sens. Heuristique volontairement simple
 * (longueur + variété de caractères) : la vraie barrière contre les mots de
 * passe faibles est la règle "uncompromised" côté serveur (voir
 * ModifierMotDePasseRequest), pas ce calcul client, purement indicatif.
 */
function evaluerForce(motDePasse: string): { libelle: string; classeTexte: string } | null {
  if (motDePasse.length === 0) {
    return null
  }

  const varietes = [/[a-z]/, /[A-Z]/, /[0-9]/, /[^a-zA-Z0-9]/].filter((motif) => motif.test(motDePasse)).length

  if (motDePasse.length < 8) {
    return { libelle: 'Trop court', classeTexte: 'text-danger' }
  }

  if (motDePasse.length >= 14 && varietes >= 3) {
    return { libelle: 'Solide', classeTexte: 'text-succes' }
  }

  if (motDePasse.length >= 10 && varietes >= 2) {
    return { libelle: 'Moyen', classeTexte: 'text-alerte' }
  }

  return { libelle: 'Faible', classeTexte: 'text-alerte' }
}

export function IndicateurForceMotDePasse({ motDePasse }: { motDePasse: string }) {
  const resultat = evaluerForce(motDePasse)

  if (!resultat) {
    return null
  }

  return (
    <p className="animate-entree-champ mt-1 text-petit text-texte-secondaire">
      Force du mot de passe : <span className={`font-medium ${resultat.classeTexte}`}>{resultat.libelle}</span>
    </p>
  )
}
