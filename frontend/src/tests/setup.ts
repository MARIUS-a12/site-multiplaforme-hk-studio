/**
 * Chargé avant chaque fichier de test (voir vitest.config.ts) : matchers
 * jest-dom (toBeInTheDocument, etc.) et nettoyage du DOM entre les tests.
 */
import { cleanup } from '@testing-library/react'
import { afterEach } from 'vitest'
import '@testing-library/jest-dom/vitest'

afterEach(() => {
  cleanup()
})
