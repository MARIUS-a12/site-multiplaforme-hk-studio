/**
 * Charge Poppins (titres) et Inter (corps) à la demande — jamais au premier
 * chargement de l'app, seulement quand le back-office est réellement monté
 * (voir RouteProtegee, le seul appelant). Un visiteur de la vitrine, lui,
 * ne déclenche jamais cette requête : elle reste sur les polices système
 * (voir index.css, --font-sans global). Idempotent : un deuxième montage de
 * RouteProtegee (navigation interne) ne réinjecte rien.
 *
 * Seules les graisses réellement utilisées sont demandées (500/600/700 pour
 * Poppins — jamais de 300/400 décoratif non utilisé ; 400/500/600 pour
 * Inter), avec affichage immédiat du texte en police système pendant le
 * chargement (display=swap) et une préconnexion au domaine des polices
 * avant même la feuille de style elle-même.
 */
const ID_BALISE = 'polices-back-office'

export function chargerPolicesBackOffice(): void {
  if (document.getElementById(ID_BALISE)) {
    return
  }

  const preconnexion1 = document.createElement('link')
  preconnexion1.rel = 'preconnect'
  preconnexion1.href = 'https://fonts.googleapis.com'

  const preconnexion2 = document.createElement('link')
  preconnexion2.rel = 'preconnect'
  preconnexion2.href = 'https://fonts.gstatic.com'
  preconnexion2.crossOrigin = 'anonymous'

  const feuilleDeStyle = document.createElement('link')
  feuilleDeStyle.id = ID_BALISE
  feuilleDeStyle.rel = 'stylesheet'
  feuilleDeStyle.href =
    'https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap'

  document.head.append(preconnexion1, preconnexion2, feuilleDeStyle)
}
