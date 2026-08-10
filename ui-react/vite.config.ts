import { defineConfig } from 'vite'
import path from 'node:path'

/**
 * Build de la brique React MelisDashboardPluginCreator.
 *
 * Produit un seul bundle IIFE (public/ui-react/brick.js) charge au runtime par le shell React de
 * MelisCore quand le module est actif. React / ReactRouter sont EXTERNES, mappes sur les globals
 * de l'hote exposes dans main.tsx de MelisCore — la brique reutilise l'instance React de l'hote
 * (hooks, context, Router fonctionnent a travers la frontiere).
 *
 * Le build est `vite build` SEUL (pas de `tsc`) : les erreurs de type ne le font pas echouer.
 */
export default defineConfig({
  esbuild: { jsx: 'automatic' },
  build: {
    outDir: path.resolve(import.meta.dirname, '..', 'public', 'ui-react'),
    // Conserve brick.manifest.json (ecrit a la main) a cote du bundle.
    emptyOutDir: false,
    lib: {
      entry: path.resolve(import.meta.dirname, 'src/brick.tsx'),
      formats: ['iife'],
      name: 'MelisDashboardPluginCreatorBrick',
      fileName: () => 'brick.js',
    },
    rollupOptions: {
      external: ['react', 'react-dom', 'react/jsx-runtime', 'react-router-dom'],
      output: {
        globals: {
          react: 'MelisReact',
          'react-dom': 'MelisReactDOM',
          'react/jsx-runtime': 'MelisReactJsxRuntime',
          'react-router-dom': 'MelisReactRouterDOM',
        },
      },
    },
  },
})
