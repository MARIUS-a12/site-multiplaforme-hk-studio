import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App.tsx'
import { FrontiereErreur } from './components/FrontiereErreur.tsx'
import './index.css'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <FrontiereErreur>
      <App />
    </FrontiereErreur>
  </StrictMode>,
)
