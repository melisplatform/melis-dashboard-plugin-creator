/**
 * Rendu des icones FontAwesome 4 du dashboard SANS dependre de FontAwesome.
 *
 * Le shell React de MelisCore ne charge PAS FontAwesome (il utilise ses propres icones). Or les
 * icones du plugin de dashboard sont des classes `fa-...` (c'est la VALEUR stockee et ecrite par
 * le generateur dans la config du plugin — le dashboard LEGACY, lui, a bien FontAwesome et les
 * affichera). La brique n'externalise que React : on ne peut donc pas s'appuyer sur un CSS hote.
 *
 * On mappe chaque classe `fa-...` vers un SVG inline equivalent (style trait, viewBox 24). Un
 * libelle lisible accompagne toujours l'icone dans le selecteur → meme sans glyphe reconnu, le
 * choix reste possible. La valeur emise/enregistree reste toujours la classe `fa-...`.
 */
import { type CSSProperties } from 'react'

const P = (d: string) => d // petit alias pour lisibilite

/** fa-class → { paths SVG, libelle lisible }. */
const ICONS: Record<string, { svg: string; label: string }> = {
  'fa-bar-chart-o': { label: 'Bar chart', svg: P('<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>') },
  'fa-calendar': { label: 'Calendar', svg: P('<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>') },
  'fa-warning': { label: 'Warning', svg: P('<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>') },
  'fa-table': { label: 'Table', svg: P('<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="12" y1="3" x2="12" y2="21"/>') },
  'fa-cog': { label: 'Cog', svg: P('<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>') },
  'fa-comment': { label: 'Comment', svg: P('<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>') },
  'fa-chain': { label: 'Link', svg: P('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>') },
  'fa-map-marker': { label: 'Map marker', svg: P('<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>') },
  'fa-trash-o': { label: 'Trash', svg: P('<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>') },
  'fa-filter': { label: 'Filter', svg: P('<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>') },
  'fa-search': { label: 'Search', svg: P('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>') },
  'fa-tag': { label: 'Tag', svg: P('<path d="M20.59 13.41 13.42 20.59a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>') },
  'fa-bookmark': { label: 'Bookmark', svg: P('<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>') },
  'fa-group': { label: 'Group', svg: P('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>') },
  'fa-bell': { label: 'Bell', svg: P('<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>') },
  'fa-clock-o': { label: 'Clock', svg: P('<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>') },
  'fa-wrench': { label: 'Wrench', svg: P('<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>') },
  'fa-ban': { label: 'Ban', svg: P('<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>') },
  'fa-share': { label: 'Share', svg: P('<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>') },
  'fa-file': { label: 'File', svg: P('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>') },
  'fa-list': { label: 'List', svg: P('<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>') },
  'fa-heart': { label: 'Heart', svg: P('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>') },
  'fa-inbox': { label: 'Inbox', svg: P('<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>') },
  'fa-envelope': { label: 'Envelope', svg: P('<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/>') },
}

const svgStyle = (size: number): CSSProperties => ({ width: size, height: size, flexShrink: 0 })

/** Rend l'icone `fa-...` en SVG inline (repli : un carre generique). */
export function FaIcon({ icon, size = 20 }: { icon: string; size?: number }) {
  const def = ICONS[icon]
  const inner = def?.svg ?? '<rect x="4" y="4" width="16" height="16" rx="3"/>'
  return (
    <svg style={svgStyle(size)} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"
         strokeLinecap="round" strokeLinejoin="round"
         dangerouslySetInnerHTML={{ __html: inner }} />
  )
}

/** Libelle lisible d'une classe `fa-...` (repli : la classe elle-meme, sans le prefixe `fa-`). */
export function iconLabel(icon: string): string {
  return ICONS[icon]?.label ?? icon.replace(/^fa-/, '').replace(/-o$/, '').replace(/-/g, ' ')
}
