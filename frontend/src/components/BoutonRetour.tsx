import { ArrowLeft } from 'lucide-react'
import { useNavigate } from 'react-router-dom'

/**
 * Bouton retour générique pour les pages qui ne sont pas l'écran principal
 * (formulaire produit, catégories) : par défaut, reprend l'historique du
 * navigateur (d'où qu'on soit venu) plutôt que de forcer une destination
 * fixe. Passe par le routeur comme toute navigation, donc respecte la
 * protection anti-perte-de-travail (useProtectionPerteTravail) si le
 * formulaire en cours a des modifications non enregistrées.
 */
export function BoutonRetour({ vers }: { vers?: string }) {
  const navigate = useNavigate()

  return (
    <button
      type="button"
      onClick={() => (vers ? navigate(vers) : navigate(-1))}
      aria-label="Retour"
      className="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded text-texte transition-colors duration-150 hover:bg-surface-alt active:bg-surface-alt focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
    >
      <ArrowLeft aria-hidden="true" size={20} strokeWidth={1.5} />
    </button>
  )
}
