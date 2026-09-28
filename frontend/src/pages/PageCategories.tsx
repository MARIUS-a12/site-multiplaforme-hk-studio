/**
 * Écran Catégories (/categories) : liste avec le nombre de produits par
 * catégorie, création en ligne (un champ + un bouton, pas une page à part —
 * ça n'a pas besoin d'être plus lourd que ça), et archivage. Accessible
 * depuis la liste de produits et depuis le champ Catégorie du formulaire
 * produit.
 */
import { useState } from 'react'
import type { FormEvent } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import type { Categorie } from '../api/categories'
import { archiverCategorie, creerCategorie } from '../api/categories'
import { BoutonRetour } from '../components/BoutonRetour'
import { EtatChargement } from '../components/EtatChargement'
import { EtatErreur } from '../components/EtatErreur'
import { useCategories } from '../hooks/useCategories'
import { extraireErreursChamps } from '../lib/erreursValidation'

export function PageCategories() {
  const { data: categories, isPending, isError, error, refetch } = useCategories()
  const queryClient = useQueryClient()

  const [nomNouvelleCategorie, setNomNouvelleCategorie] = useState('')
  const [erreurCreation, setErreurCreation] = useState<string | null>(null)

  const creation = useMutation({
    mutationFn: () => creerCategorie(nomNouvelleCategorie.trim()),
    onSuccess: () => {
      setNomNouvelleCategorie('')
      setErreurCreation(null)
      queryClient.invalidateQueries({ queryKey: ['categories'] })
    },
    onError: (erreur) => {
      const champs = extraireErreursChamps(erreur)
      setErreurCreation(
        champs.nom ?? "Impossible de créer la catégorie. Vérifiez votre connexion et réessayez.",
      )
    },
  })

  const archivage = useMutation({
    mutationFn: (id: number) => archiverCategorie(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['categories'] }),
  })

  function soumettreCreation(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault()
    if (!nomNouvelleCategorie.trim()) {
      return
    }
    creation.mutate()
  }

  function demanderArchivage(categorie: Categorie) {
    const confirme = window.confirm(
      `Les produits de « ${categorie.nom} » ne seront pas supprimés, seulement détachés ` +
        `visuellement de cette catégorie.\n\nArchiver « ${categorie.nom} » ?`,
    )

    if (confirme) {
      archivage.mutate(categorie.id)
    }
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div className="flex items-center gap-2">
        <BoutonRetour />
        <h1 className="text-titre-page font-semibold text-texte">Catégories</h1>
      </div>

      <form onSubmit={soumettreCreation} className="flex gap-3">
        <div className="flex-1">
          <input
            type="text"
            placeholder="Nom de la nouvelle catégorie"
            value={nomNouvelleCategorie}
            onChange={(evenement) => {
              setNomNouvelleCategorie(evenement.target.value)
              setErreurCreation(null)
            }}
            className={`h-11 w-full rounded border bg-surface px-3 text-corps text-texte transition-colors duration-150 focus:outline focus:outline-2 focus:outline-primaire focus:outline-offset-1 ${
              erreurCreation ? 'border-danger' : 'border-bordure focus:border-primaire'
            }`}
          />
          {erreurCreation && <p className="mt-1 text-petit text-danger">{erreurCreation}</p>}
        </div>
        <button
          type="submit"
          disabled={creation.isPending || !nomNouvelleCategorie.trim()}
          className="h-11 shrink-0 cursor-pointer rounded bg-primaire px-4 text-corps font-medium text-surface transition-colors duration-150 hover:bg-primaire-fonce active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {creation.isPending ? 'Ajout…' : 'Ajouter'}
        </button>
      </form>

      {isPending && <EtatChargement />}

      {isError && <EtatErreur erreur={error} onReessayer={() => refetch()} />}

      {!isPending && !isError && categories && categories.length === 0 && (
        <p className="text-corps text-texte-secondaire">Aucune catégorie pour le moment.</p>
      )}

      {!isPending && !isError && categories && categories.length > 0 && (
        <ul className="divide-y divide-bordure border border-bordure">
          {categories.map((categorie) => (
            <li key={categorie.id} className="flex items-center justify-between gap-3 p-4">
              <div>
                <span
                  className={`text-corps font-medium ${categorie.statut === 'inactif' ? 'text-texte-secondaire' : 'text-texte'}`}
                >
                  {categorie.nom}
                </span>
                {categorie.statut === 'inactif' && (
                  <span className="ml-2 text-petit text-texte-secondaire">Archivée</span>
                )}
                <div className="tabular-nums text-petit text-texte-secondaire">
                  {categorie.produits_count ?? 0} produit
                  {(categorie.produits_count ?? 0) === 1 ? '' : 's'}
                </div>
              </div>

              {categorie.statut === 'actif' && (
                <button
                  type="button"
                  onClick={() => demanderArchivage(categorie)}
                  disabled={archivage.isPending}
                  className="cursor-pointer text-petit font-medium text-danger underline disabled:opacity-60"
                >
                  Archiver
                </button>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
