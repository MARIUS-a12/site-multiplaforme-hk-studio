/**
 * Révélation unique du mot de passe généré à la création d'un
 * établissement — jamais récupérable ensuite (voir api/etablissements.ts).
 * Ton d'avertissement volontairement plus appuyé que BandeauSucces : c'est
 * la dernière fois que ce mot de passe sera visible.
 */
import { Check, Copy, X } from 'lucide-react'
import { useState } from 'react'

export function BandeauMotDePasseGenere({
  motDePasse,
  onFermer,
}: {
  motDePasse: string
  onFermer: () => void
}) {
  const [copie, setCopie] = useState(false)

  async function copier() {
    await navigator.clipboard.writeText(motDePasse)
    setCopie(true)
  }

  return (
    <div className="border border-alerte bg-alerte/10 p-4">
      <div className="flex items-start justify-between gap-3">
        <p className="text-corps font-semibold text-texte">
          Mot de passe de l'administrateur créé — à noter maintenant
        </p>
        <button
          type="button"
          onClick={onFermer}
          aria-label="Fermer"
          className="-m-1.5 flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded text-texte-secondaire transition-[background-color,transform] hover:bg-alerte/10 active:scale-[0.97] focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
        >
          <X aria-hidden="true" size={20} strokeWidth={1.5} />
        </button>
      </div>

      <p className="mt-1 text-petit text-texte-secondaire">
        Ce mot de passe ne sera plus jamais affiché. Transmettez-le à l'administrateur de la main à
        la main ou par WhatsApp avant de fermer ce bandeau.
      </p>

      <div className="mt-3 flex items-center gap-2">
        <code className="h-11 flex-1 rounded border border-bordure bg-surface px-3 text-corps tabular-nums text-texte flex items-center">
          {motDePasse}
        </code>
        <button
          type="button"
          onClick={copier}
          className="flex h-11 shrink-0 cursor-pointer items-center gap-1.5 rounded bg-primaire px-3 text-corps font-medium text-surface transition-[background-color,transform] hover:bg-primaire-fonce active:scale-[0.97] active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
        >
          {copie ? (
            <Check aria-hidden="true" size={20} strokeWidth={1.5} />
          ) : (
            <Copy aria-hidden="true" size={20} strokeWidth={1.5} />
          )}
          {copie ? 'Copié' : 'Copier'}
        </button>
      </div>
    </div>
  )
}
