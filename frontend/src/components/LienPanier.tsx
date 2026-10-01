/**
 * Icône panier de l'en-tête vitrine, avec le nombre d'articles — apparaît dès
 * le premier ajout (voir usePanier, synchronisé avec tout composant qui
 * modifie le panier, pas seulement cette page).
 */
import { ShoppingCart } from 'lucide-react'
import { Link } from 'react-router-dom'
import { usePanier } from '../hooks/usePanier'

export function LienPanier() {
  const { nombreArticles } = usePanier()

  return (
    <Link
      to="/panier"
      aria-label={nombreArticles > 0 ? `Panier, ${nombreArticles} article(s)` : 'Panier'}
      className="relative flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded text-texte transition-[background-color,transform] hover:bg-surface-alt active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-texte focus-visible:outline-offset-1"
    >
      <ShoppingCart aria-hidden="true" size={22} strokeWidth={1.5} />
      {nombreArticles > 0 && (
        <span
          aria-hidden="true"
          className="absolute right-0 top-0 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-accent px-1 text-petit font-semibold leading-none tabular-nums text-accent-texte"
        >
          {nombreArticles > 99 ? '99+' : nombreArticles}
        </span>
      )}
    </Link>
  )
}
