import react from '@vitejs/plugin-react'
import { defineConfig } from 'vitest/config'

// Config de test séparée de vite.config.ts (build de prod) : les deux n'ont
// besoin de partager que le plugin React, pas allowedHosts ni Tailwind.
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/tests/setup.ts'],
  },
})
