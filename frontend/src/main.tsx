// Polices chargées localement (fichiers dans node_modules/@fontsource, servis
// par notre propre build) plutôt que depuis Google Fonts : le commerçant peut
// être en connexion lente ou coupée, l'app ne doit dépendre d'aucun CDN tiers.
import '@fontsource/inter/400.css'
import '@fontsource/inter/500.css'
import '@fontsource/inter/600.css'

import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App.tsx'
import './index.css'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
