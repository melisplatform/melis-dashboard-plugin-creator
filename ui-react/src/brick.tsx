import DpcPage from './DpcPage'

/**
 * Point d'entree de la brique. Quand ce bundle IIFE est charge par le shell MelisCore, il
 * auto-enregistre son composant de page sous l'id de la brique. L'hote le monte sur la route
 * derivee de l'arbre de menu (via `forwardKey`), la route du manifeste servant de repli.
 */
declare global {
  interface Window {
    __melisRegisterBrick?: (b: { id: string; Component: unknown }) => void
  }
}

window.__melisRegisterBrick?.({ id: 'dashboard-plugin-creator', Component: DpcPage })
