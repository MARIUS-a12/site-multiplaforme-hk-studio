/**
 * Révélation unique du code d'activation généré pour un membre de l'équipe
 * (création ou "Générer un nouveau code d'accès") — jamais récupérable
 * ensuite, voir api/equipe.ts. Même esprit que BandeauMotDePasseGenere
 * (établissement) : ce n'est PAS un mot de passe, l'administrateur ne le
 * connaît jamais, seulement un code à transmettre, valable 48 heures.
 */
import { Check, Copy, X } from 'lucide-react'
import { useState } from 'react'

export function BandeauCodeActivationGenere({ code, onFermer }: { code: string; onFermer: () => void }) {
  const [copie, setCopie] = useState(false)

  async function copier() {
    await navigator.clipboard.writeText(code)
    setCopie(true)
  }

  return (
    <div className="border border-alerte bg-alerte/10 p-4">
      <div className="flex items-start justify-between gap-3">
        <p className="text-corps font-semibold text-texte">Code d'activation généré — à transmettre maintenant</p>
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
        Ce code ne sera plus jamais affiché. Donnez-le à la personne concernée de la main à la main ou par
        téléphone — il expire dans 48 heures. Elle l'utilisera sur /admin/activation pour choisir elle-même son mot
        de passe.
      </p>

      <div className="mt-3 flex items-center gap-2">
        <code className="h-14 flex-1 rounded border border-bordure bg-surface px-3 text-titre-section tabular-nums tracking-[0.3em] text-texte flex items-center justify-center">
          {code}
        </code>
        <button
          type="button"
          onClick={copier}
          className="flex h-14 shrink-0 cursor-pointer items-center gap-1.5 rounded bg-primaire px-3 text-corps font-medium text-surface transition-[background-color,transform] hover:bg-primaire-fonce active:scale-[0.97] active:bg-primaire-fonce focus-visible:outline focus-visible:outline-2 focus-visible:outline-primaire focus-visible:outline-offset-1"
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
