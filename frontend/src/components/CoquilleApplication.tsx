/**
 * Coquille visuelle de l'app une fois connecté : en-tête fixe (nom de
 * l'établissement, menu utilisateur) + zone de contenu. Rendue par
 * RouteProtegee, jamais montée directement par une route. "Paramètres" ne
 * vit plus ici en icône isolée : il est entré dans le menu utilisateur (voir
 * MenuUtilisateur) avec Mon compte et Paiement. Pas de navigation principale
 * dans l'en-tête (Produits/Catégories) : chaque page y mène depuis ailleurs.
 */
import type { ReactNode } from 'react'
import type { Moi } from '../api/auth'
import { CarreInitiale } from './CarreInitiale'
import { MenuUtilisateur } from './MenuUtilisateur'
import { TransitionPage } from './TransitionPage'

export function CoquilleApplication({ moi, children }: { moi: Moi; children: ReactNode }) {
  const nomEtablissement = moi.etablissement?.nom ?? 'Back-office'

  return (
    <div className="min-h-screen">
      <header className="sticky top-0 z-10 flex h-14 items-center justify-between gap-3 border-b border-bordure bg-surface px-4">
        <div className="flex items-center gap-3">
          <CarreInitiale nom={nomEtablissement} taille={40} />
          <span className="truncate text-corps font-semibold text-texte">{nomEtablissement}</span>
        </div>

        <MenuUtilisateur moi={moi} />
      </header>

      <main className="p-4">
        <TransitionPage>{children}</TransitionPage>
      </main>
    </div>
  )
}
