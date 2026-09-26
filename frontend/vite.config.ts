import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    // L'app doit être ouverte sur un sous-domaine (chez-awa.localhost), pas
    // sur localhost : Vite refuse par défaut les requêtes dont l'en-tête
    // Host n'est pas explicitement autorisé (protection anti DNS-rebinding).
    allowedHosts: true,
  },
})
