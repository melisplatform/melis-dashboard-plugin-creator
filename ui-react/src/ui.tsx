/**
 * Primitives partagees par la brique Dashboard Plugin Creator (styles inline + variables CSS du
 * theme de l'hote, i18n FR/EN lue depuis `<html lang>`). La brique ne peut PAS importer les modules
 * de l'hote (Tailwind/shadcn/lucide/i18n) : le bundle n'externalise que React → tout est autonome ici.
 */
import { useState, type CSSProperties, type ReactNode } from 'react'
import type { IconOption } from './dpc-api'
import { FaIcon, iconLabel } from './icons'

/* ── i18n ─────────────────────────────────────────────────────────────────── */
export type Lang = 'fr' | 'en'
export function currentLang(): Lang {
  const l = (document.documentElement.lang || 'en').toLowerCase()
  return l.startsWith('fr') ? 'fr' : 'en'
}

const DICT: Record<Lang, Record<string, string>> = {
  fr: {
    title: 'Créateur de plugin de dashboard',
    subtitle: 'Génère un plugin de tableau de bord prêt à l’emploi, dans un module nouveau ou existant',
    // commun
    back: 'Précédent', next: 'Suivant', cancel: 'Annuler', restart: 'Recommencer',
    loading: 'Chargement…', saving: 'Enregistrement…', required: 'Obligatoire', optional: 'Facultatif',
    yes: 'Oui', no: 'Non', none: 'Aucun', close: 'Fermer', errors_title: 'Veuillez corriger les erreurs',
    readonly_notice: 'Vous n’avez pas le droit de modifier cet assistant : il est en lecture seule.',
    no_access: 'Vous n’avez pas les droits pour consulter cet outil.',
    blocking_title: 'Configuration du serveur incomplète',
    blocking_desc: 'L’outil ne peut pas générer de plugin tant que ces points ne sont pas corrigés :',
    // étape 1
    s1_name: 'Nom du plugin', s1_type: 'Type d’affichage',
    s1_single: 'Simple', s1_multi: 'Multi-onglets',
    s1_tab_count: 'Nombre d’onglets', s1_tab_count_hint: 'Entre {min} et {max}.',
    s1_dest: 'Destination du plugin', s1_new: 'Nouveau module', s1_existing: 'Module existant',
    s1_new_name: 'Nom du nouveau module', s1_existing_name: 'Module', s1_choose: 'Choisissez…',
    s1_no_modules: 'Aucun module existant utilisable : créez un nouveau module.',
    // étape 2
    s2_desc: 'Titre et description affichés dans le menu déroulant de droite, par langue. Au moins une langue doit être renseignée.',
    s2_title_f: 'Titre du plugin', s2_desc_f: 'Description',
    s2_thumb: 'Vignette du plugin', s2_thumb_hint: 'Formats : GIF, JPG, PNG — 190×100 recommandé, {max} maximum.',
    s2_thumb_pick: 'Choisir une image', s2_thumb_remove: 'Retirer la vignette',
    s2_thumb_missing: 'Une vignette est requise pour générer le plugin.',
    // étape 3
    s3_desc: 'Titre affiché sur la carte du plugin (par langue) et icônes du plugin.',
    s3_title_f: 'Titre du plugin',
    s3_icon: 'Icône du plugin', s3_icon_hint: 'Icône affichée en haut à gauche de la carte du dashboard.',
    s3_tab_icons: 'Icônes des onglets', s3_tab: 'Onglet {n}',
    s3_pick_icon: 'Choisissez une icône',
    // étape 4
    s4_desc: 'Vérifiez la configuration avant de générer le plugin.',
    s4_plugin: 'Plugin', s4_target: 'Module cible', s4_type: 'Type', s4_tabs: 'Onglets',
    s4_menu: 'Textes du menu', s4_dashboard: 'Titres du dashboard', s4_icon: 'Icône', s4_tab_icons: 'Icônes des onglets',
    s4_no_access: 'Vous n’avez pas le droit de consulter le récapitulatif.',
    // étape 5
    s5_desc: 'Lancez la génération. Cochez la case pour activer le plugin dès sa création.',
    s5_activate: 'Activer le plugin après création',
    s5_activate_note: 'L’activation nécessite un rechargement de la plateforme.',
    s5_module_note: 'Le module « {m} » devra être activé pour afficher le plugin sur le dashboard.',
    s5_generate: 'Terminer et créer le plugin', s5_generating: 'Génération en cours…',
    s5_no_access: 'Vous n’avez pas le droit de générer un plugin.',
    ok_title: 'Le plugin a été créé', ok_module: 'Module : {m}', ok_plugin: 'Plugin : {p}',
    ok_reload: 'La plateforme va se recharger dans {n}…', ok_manual: 'Rechargez la page pour activer le plugin.',
    ok_reload_now: 'Recharger maintenant', ok_new: 'Créer un autre plugin',
    old_resets: 'L’ancienne interface réinitialise l’assistant : le brouillon en cours sera perdu. Continuer ?',
  },
  en: {
    title: 'Dashboard Plugin Creator',
    subtitle: 'Generates a ready-to-use dashboard plugin, in a new or existing module',
    back: 'Back', next: 'Next', cancel: 'Cancel', restart: 'Start over',
    loading: 'Loading…', saving: 'Saving…', required: 'Required', optional: 'Optional',
    yes: 'Yes', no: 'No', none: 'None', close: 'Close', errors_title: 'Please fix the errors below',
    readonly_notice: 'You are not allowed to edit this wizard: it is read-only.',
    no_access: 'You do not have permission to view this tool.',
    blocking_title: 'Incomplete server configuration',
    blocking_desc: 'The tool cannot generate a plugin until these are fixed:',
    s1_name: 'Plugin name', s1_type: 'View type',
    s1_single: 'Single', s1_multi: 'Multi-tabs',
    s1_tab_count: 'Tab count', s1_tab_count_hint: 'Between {min} and {max}.',
    s1_dest: 'Plugin destination', s1_new: 'New module', s1_existing: 'Existing module',
    s1_new_name: 'New module name', s1_existing_name: 'Module', s1_choose: 'Choose…',
    s1_no_modules: 'No usable existing module: create a new module.',
    s2_desc: 'Title and description shown in the right expandable menu, per language. At least one language is required.',
    s2_title_f: 'Plugin title', s2_desc_f: 'Description',
    s2_thumb: 'Plugin thumbnail', s2_thumb_hint: 'Formats: GIF, JPG, PNG — 190×100 recommended, {max} max.',
    s2_thumb_pick: 'Choose an image', s2_thumb_remove: 'Remove thumbnail',
    s2_thumb_missing: 'A thumbnail is required to generate the plugin.',
    s3_desc: 'Title shown on the plugin card (per language) and the plugin icons.',
    s3_title_f: 'Plugin title',
    s3_icon: 'Plugin icon', s3_icon_hint: 'Icon shown in the top-left corner of the dashboard card.',
    s3_tab_icons: 'Tab icons', s3_tab: 'Tab {n}',
    s3_pick_icon: 'Pick an icon',
    s4_desc: 'Check the configuration before generating the plugin.',
    s4_plugin: 'Plugin', s4_target: 'Target module', s4_type: 'Type', s4_tabs: 'Tabs',
    s4_menu: 'Menu texts', s4_dashboard: 'Dashboard titles', s4_icon: 'Icon', s4_tab_icons: 'Tab icons',
    s4_no_access: 'You are not allowed to view the summary.',
    s5_desc: 'Run the generation. Tick the box to activate the plugin on creation.',
    s5_activate: 'Activate plugin after creation',
    s5_activate_note: 'Activating requires a platform reload.',
    s5_module_note: 'The “{m}” module will have to be activated to display the plugin on the dashboard.',
    s5_generate: 'Finish and create the plugin', s5_generating: 'Generating…',
    s5_no_access: 'You are not allowed to generate a plugin.',
    ok_title: 'The plugin has been created', ok_module: 'Module: {m}', ok_plugin: 'Plugin: {p}',
    ok_reload: 'The platform will reload in {n}…', ok_manual: 'Reload the page to activate the plugin.',
    ok_reload_now: 'Reload now', ok_new: 'Create another plugin',
    old_resets: 'The old interface resets the wizard: your current draft will be lost. Continue?',
  },
}

export type TFn = (key: string, vars?: Record<string, string | number>) => string
export function useT(): TFn {
  const lang = currentLang()
  return (key, vars) => {
    let s = DICT[lang][key] ?? key
    if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v))
    return s
  }
}

/** Toast vers le chrome React de l'hote (meme canal que buildToolPage). */
export function notify(kind: 'ok' | 'ko', title: string, message: string) {
  window.postMessage({ __melisNotif: true, kind, title, message }, '*')
}

export function formatBytes(bytes: number): string {
  const units = ['B', 'kB', 'MB', 'GB']
  const i = bytes > 0 ? Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1) : 0
  return `${Math.round((bytes / 1024 ** i) * 100) / 100} ${units[i]}`
}

/* ── Styles ───────────────────────────────────────────────────────────────── */
export const card: CSSProperties = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)' }
export const inputCss: CSSProperties = { height: 40, width: '100%', boxSizing: 'border-box', borderRadius: 8, border: '1px solid var(--color-input,var(--color-border))', background: 'var(--color-card)', color: 'var(--color-foreground)', padding: '0 12px', fontSize: 14, outline: 'none' }
export const textareaCss: CSSProperties = { ...inputCss, height: 'auto', minHeight: 88, padding: '10px 12px', resize: 'vertical', fontFamily: 'inherit' }
export const btnPrimary: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 14px', borderRadius: 8, border: 0, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', fontSize: 14, fontWeight: 500, cursor: 'pointer' }
export const btnGhost: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 12px', borderRadius: 8, border: '1px solid var(--color-border)', background: 'var(--color-card)', color: 'var(--color-foreground)', fontSize: 14, cursor: 'pointer' }
export const label: CSSProperties = { display: 'block', fontSize: 13, fontWeight: 500, marginBottom: 4, color: 'var(--color-foreground)' }
export const hint: CSSProperties = { marginTop: 4, fontSize: 12, color: 'var(--color-muted-foreground)' }
export const th: CSSProperties = { textAlign: 'left', padding: '8px 14px', fontSize: 11, fontWeight: 600, textTransform: 'uppercase', letterSpacing: '.04em', color: 'var(--color-muted-foreground)', whiteSpace: 'nowrap' }
export const td: CSSProperties = { padding: '8px 14px', fontSize: 14, color: 'var(--color-foreground)', borderTop: '1px solid var(--color-border)', verticalAlign: 'top' }

/* ── Icones (SVG inline, comme la brique templating) ──────────────────────── */
const sIcon = { width: 15, height: 15, flexShrink: 0 } as const
export const CheckIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
export const TrashIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /></svg>
export const ArrowLeft = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
export const ArrowRight = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
export const RotateIcon = () => <svg style={sIcon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 2v6h6" /><path d="M3 13a9 9 0 1 0 3-7.7L3 8" /></svg>
export const SpinIcon = () => (
  <svg style={{ ...sIcon, animation: 'melis-dpc-spin 1s linear infinite' }} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
    <path d="M21 12a9 9 0 1 1-6.2-8.6" />
  </svg>
)

/* ── Composants ───────────────────────────────────────────────────────────── */

export function Field({ label: lbl, required, hint: h, error, children }: {
  label: string; required?: boolean; hint?: string; error?: string[]; children: ReactNode
}) {
  return (
    <div>
      <label style={label}>
        {lbl}{required && <span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>}
      </label>
      {children}
      {h && <p style={hint}>{h}</p>}
      {error?.map((m, i) => (
        <p key={i} style={{ marginTop: 4, fontSize: 12, color: 'var(--color-destructive,#ef4444)' }}
           // Les messages legacy contiennent parfois du HTML : ils viennent des fichiers de langue
           // du module, jamais d'une saisie utilisateur.
           dangerouslySetInnerHTML={{ __html: m }} />
      ))}
    </div>
  )
}

export function ErrorBanner({ title, messages }: { title: string; messages: string[] }) {
  if (!messages.length) return null
  return (
    <div style={{ ...card, borderColor: '#fca5a5', background: 'color-mix(in srgb, #ef4444 8%, var(--color-card))', padding: '10px 14px' }}>
      <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: '#b91c1c' }}>{title}</p>
      <ul style={{ margin: '6px 0 0', paddingLeft: 18, fontSize: 13, color: '#b91c1c' }}>
        {messages.map((m, i) => <li key={i} dangerouslySetInnerHTML={{ __html: m }} />)}
      </ul>
    </div>
  )
}

export function Notice({ children, tone = 'info' }: { children: ReactNode; tone?: 'info' | 'warn' }) {
  const color = tone === 'warn' ? '#b45309' : 'var(--color-muted-foreground)'
  const bg = tone === 'warn' ? 'color-mix(in srgb, #f59e0b 10%, var(--color-card))' : 'color-mix(in srgb, var(--color-muted,#888) 8%, transparent)'
  return <div style={{ ...card, background: bg, padding: '10px 14px', fontSize: 13, color }}>{children}</div>
}

export function Toggle({ on, onClick, disabled: off }: { on: boolean; onClick: () => void; disabled?: boolean }) {
  return (
    <button type="button" onClick={onClick} disabled={off} style={{
      width: 44, height: 24, borderRadius: 999, border: 0, cursor: off ? 'not-allowed' : 'pointer', padding: 2,
      opacity: off ? 0.5 : 1, background: on ? 'var(--color-primary)' : 'var(--color-border)', transition: 'background .15s',
    }}>
      <span style={{ display: 'block', width: 20, height: 20, borderRadius: '50%', background: '#fff', transform: on ? 'translateX(20px)' : 'translateX(0)', transition: 'transform .15s' }} />
    </button>
  )
}

/**
 * Drapeau de la langue. Images livrees par MelisCore (`public/images/lang/<locale>.png`, servies
 * par MelisAssetManager sous /MelisCore/) : les emojis drapeaux ne se rendent pas sous Windows.
 * Locale inconnue (pas de png) → l'image se masque, la pastille garde juste son libelle.
 */
function LangFlag({ locale, dim }: { locale: string; dim?: boolean }) {
  return (
    <img
      src={`/MelisCore/images/lang/${locale}.png`}
      alt=""
      width={16}
      height={11}
      style={{
        display: 'block', borderRadius: 2, objectFit: 'cover', flexShrink: 0,
        // Langue non selectionnee : drapeau desature → la pastille active ressort au premier coup d'oeil.
        filter: dim ? 'grayscale(1)' : 'none', opacity: dim ? 0.55 : 1, transition: 'filter .15s, opacity .15s',
      }}
      onError={(e) => { (e.currentTarget as HTMLImageElement).style.display = 'none' }}
    />
  )
}

/**
 * Selecteur de langue en pastilles (etapes 2 et 3).
 *
 * L'etat ACTIF doit rester lisible sur une carte blanche : un simple fond `--color-card` ne se
 * distingue pas du panneau. On cumule donc quatre signaux → teinte primaire, contour primaire,
 * libelle en gras dans la couleur primaire, drapeau des autres langues desature (+ survol).
 * `aria-pressed` porte le meme etat pour les lecteurs d'ecran.
 */
export function LangTabs({ langs, active, onChange, filled }: {
  langs: { locale: string; name: string }[]; active: string; onChange: (l: string) => void; filled?: (locale: string) => boolean
}) {
  const [hover, setHover] = useState<string | null>(null)
  return (
    <div style={{ display: 'inline-flex', gap: 4, padding: 4, borderRadius: 8, border: '1px solid var(--color-border)', background: 'color-mix(in srgb, var(--color-muted,#888) 12%, transparent)' }}>
      {langs.map((l) => {
        const on = l.locale === active
        const hot = !on && hover === l.locale
        return (
          <button key={l.locale} type="button" aria-pressed={on} title={l.name}
            onClick={() => onChange(l.locale)}
            onMouseEnter={() => setHover(l.locale)}
            onMouseLeave={() => setHover((h) => (h === l.locale ? null : h))}
            style={{
              display: 'inline-flex', alignItems: 'center', gap: 6, height: 28, padding: '0 12px', borderRadius: 6, border: 0,
              fontSize: 12, fontWeight: on ? 600 : 500, cursor: 'pointer',
              background: on
                ? 'color-mix(in srgb, var(--color-primary) 14%, var(--color-card))'
                : hot ? 'color-mix(in srgb, var(--color-muted,#888) 18%, transparent)' : 'transparent',
              color: on ? 'var(--color-primary)' : 'var(--color-foreground)',
              // `inset` plutot qu'une bordure : pas de decalage de 1px entre pastille active et inactive.
              boxShadow: on ? 'inset 0 0 0 1px var(--color-primary), 0 1px 2px rgba(0,0,0,.06)' : 'none',
              transition: 'background .15s, color .15s, box-shadow .15s',
            }}>
            {filled?.(l.locale) && <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#22c55e' }} />}
            <LangFlag locale={l.locale} dim={!on} />
            {l.name}
          </button>
        )
      })}
    </div>
  )
}

/**
 * Grille de selection d'icone (icone du plugin + icones d'onglet). La valeur emise est celle que le
 * generateur ecrira : classe `fa-...` pour le plugin, classe Glyphicons pour un onglet. L'apercu est
 * toujours un SVG inline derive de la classe `fa-...` (cf. icons.tsx : l'hote n'a pas FontAwesome).
 * Une entree simple (string) vaut `{ value: s, preview: s }`.
 */
export function IconGrid({ icons, value, onChange, disabled }: {
  icons: Array<string | IconOption>; value: string; onChange: (v: string) => void; disabled?: boolean
}) {
  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(84px, 1fr))', gap: 8 }}>
      {icons.map((opt) => {
        const item = typeof opt === 'string' ? { value: opt, preview: opt } : opt
        const icon = item.preview
        const on = item.value === value
        return (
          <button key={item.value} type="button" title={iconLabel(icon)} disabled={disabled}
                  onClick={() => onChange(item.value)} style={{
            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6, padding: '10px 6px', borderRadius: 8,
            cursor: disabled ? 'not-allowed' : 'pointer', opacity: disabled ? 0.6 : 1,
            border: `1px solid ${on ? 'var(--color-primary)' : 'var(--color-border)'}`,
            background: on ? 'color-mix(in srgb, var(--color-primary) 10%, transparent)' : 'var(--color-card)',
            color: on ? 'var(--color-primary)' : 'var(--color-foreground)',
          }}>
            <FaIcon icon={icon} size={22} />
            <span style={{ fontSize: 10, color: 'var(--color-muted-foreground)', textAlign: 'center', lineHeight: 1.2, wordBreak: 'break-word' }}>{iconLabel(icon)}</span>
          </button>
        )
      })}
    </div>
  )
}

/** Volet monte en permanence, montre/cache en CSS — ne remonte pas (ni refetch, ni perte de saisie). */
export function Pane({ show, children }: { show: boolean; children: ReactNode }) {
  return <div style={{ display: show ? 'block' : 'none' }}>{children}</div>
}

/** Keyframes du spinner : la brique ne dispose pas du CSS de l'hote. */
export function BrickStyles() {
  return <style>{`@keyframes melis-dpc-spin{to{transform:rotate(360deg)}}`}</style>
}

export { FaIcon, iconLabel }
