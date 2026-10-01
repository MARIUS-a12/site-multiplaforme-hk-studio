/**
 * <img> (dans un <picture> si un srcset webp est fourni) qui apparaît en
 * fondu à la fin de son chargement plutôt que de surgir brutalement.
 * Dimensions toujours fixées en attributs (largeur/hauteur), pour que rien
 * ne se décale pendant que l'image charge — l'espace est réservé qu'elle
 * soit visible ou encore transparente.
 */
import { useState } from 'react'

export function ImageAvecFondu({
  srcJpg,
  srcSetWebp,
  sizes,
  alt,
  width,
  height,
  loading = 'lazy',
  className = '',
}: {
  srcJpg: string | undefined
  srcSetWebp?: string
  sizes?: string
  alt: string
  width: number
  height: number
  loading?: 'lazy' | 'eager'
  className?: string
}) {
  const [chargee, setChargee] = useState(false)

  return (
    <picture>
      {srcSetWebp && <source type="image/webp" srcSet={srcSetWebp} sizes={sizes} />}
      <img
        src={srcJpg}
        alt={alt}
        loading={loading}
        width={width}
        height={height}
        onLoad={() => setChargee(true)}
        className={`transition-opacity duration-normale ease-apparition ${chargee ? 'opacity-100' : 'opacity-0'} ${className}`}
      />
    </picture>
  )
}
