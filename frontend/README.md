# React + TypeScript + Vite

This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

## Configuration

Copiez `.env.example` vers `.env` et renseignez `VITE_API_PORT` (8000 en
développement local avec `php artisan serve`, vide en production).

**Il n'y a pas de `VITE_API_URL`, volontairement.** Ce back-office est
multi-établissements : chaque établissement a son propre sous-domaine
(`chez-awa.localhost`, `maquis-du-port.localhost`, …), et c'est ce
sous-domaine — celui réellement ouvert dans le navigateur — qui détermine
de quel établissement il s'agit, côté API comme côté frontend. L'API vit
donc toujours sur ce même sous-domaine, jamais sur une adresse fixe.
L'adresse de base est construite au chargement à partir de
`window.location.protocol` + `window.location.hostname` + `VITE_API_PORT`
(voir `src/lib/client.ts`). Une valeur en dur du type
`VITE_API_URL=http://chez-awa.localhost:8000` enverrait toutes les
requêtes à l'établissement "chez-awa" même en ouvrant le site sur
`maquis-du-port.localhost` — le frontend ne fonctionnerait alors que pour
un seul établissement.

Pour tester un autre établissement en local, ouvrez simplement l'app sur
son sous-domaine (ex. `http://maquis-du-port.localhost:5173`) : aucune
configuration à changer, le port d'API reste le même pour tous les
établissements en développement.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) uses [Oxc](https://oxc.rs)
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) uses [SWC](https://swc.rs/)

## React Compiler

The React Compiler is not enabled on this template because of its impact on dev & build performances. To add it, see [this documentation](https://react.dev/learn/react-compiler/installation).

## Expanding the ESLint configuration

If you are developing a production application, we recommend updating the configuration to enable type-aware lint rules:

```js
export default defineConfig([
  globalIgnores(['dist']),
  {
    files: ['**/*.{ts,tsx}'],
    extends: [
      // Other configs...

      // Remove tseslint.configs.recommended and replace with this
      tseslint.configs.recommendedTypeChecked,
      // Alternatively, use this for stricter rules
      tseslint.configs.strictTypeChecked,
      // Optionally, add this for stylistic rules
      tseslint.configs.stylisticTypeChecked,

      // Other configs...
    ],
    languageOptions: {
      parserOptions: {
        project: ['./tsconfig.node.json', './tsconfig.app.json'],
        tsconfigRootDir: import.meta.dirname,
      },
      // other options...
    },
  },
])

```

You can also install [eslint-plugin-react-x](https://npmx.dev/package/eslint-plugin-react-x) and [eslint-plugin-react-dom](https://npmx.dev/package/eslint-plugin-react-dom) for React-specific lint rules:

```js
// eslint.config.js
import reactX from 'eslint-plugin-react-x'
import reactDom from 'eslint-plugin-react-dom'

export default defineConfig([
  globalIgnores(['dist']),
  {
    files: ['**/*.{ts,tsx}'],
    extends: [
      // Other configs...
      // Enable lint rules for React
      reactX.configs['recommended-typescript'],
      // Enable lint rules for React DOM
      reactDom.configs.recommended,
    ],
    languageOptions: {
      parserOptions: {
        project: ['./tsconfig.node.json', './tsconfig.app.json'],
        tsconfigRootDir: import.meta.dirname,
      },
      // other options...
    },
  },
])

```
