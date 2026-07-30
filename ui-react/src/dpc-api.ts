/**
 * Client type des endpoints `/melis/react-api/dpc/*` (controleur du module).
 *
 * Le back-office Melis exige l'en-tete `X-Requested-With` sur ses routes JSON. Le contrat est
 * toujours `{ success, data, error }` : `apiFetch` leve sur `success:false` (erreur technique ou
 * refus de droits) — MAIS une VALIDATION echouee n'est pas une erreur : elle revient en
 * `data.valid:false` + `data.errors`, que l'appelant affiche champ par champ.
 */

const XHR: HeadersInit = { 'X-Requested-With': 'XMLHttpRequest' }
const BASE = '/melis/react-api/dpc'

interface Envelope<T> { success: boolean; data: T; error?: string }

async function apiFetch<T>(path: string, init?: RequestInit): Promise<T> {
  const res = await fetch(`${BASE}${path}`, {
    credentials: 'same-origin',
    ...init,
    headers: { ...XHR, ...(init?.headers ?? {}) },
  })
  let body: Envelope<T>
  try {
    body = (await res.json()) as Envelope<T>
  } catch {
    throw new Error(`HTTP ${res.status}`)
  }
  if (!body.success) throw new Error(body.error || `HTTP ${res.status}`)
  return body.data
}

const postJson = <T>(path: string, payload: unknown) =>
  apiFetch<T>(path, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })

// ─── Types ───────────────────────────────────────────────────────────────────

export interface Language { id: number; locale: string; name: string }
export interface StepMeta { key: string; name: string; icon: string }

export interface Context {
  /** Non vide ⇒ l'environnement interdit la generation (droits FS) : on n'affiche que ca. */
  blocking: string[]
  steps: StepMeta[]
  languages: Language[]
  currentLocale: string
  /** Modules existants proposes au select (destination « module existant »). */
  siteModules: string[]
  /** Icones proposees pour l'icone du plugin (classes `fa-...`). */
  pluginIcons: string[]
  /** Icones proposees pour les onglets (classes `fa-...`). */
  tabIcons: string[]
  minTabs: number
  maxTabs: number
  thumbnail: { minSize: number; maxSize: number; accept: string }
}

export interface Step1Data {
  dpc_plugin_name?: string
  dpc_plugin_type?: string          // 'single' | 'multi'
  dpc_tab_count?: string
  dpc_plugin_destination?: string   // 'new_module' | 'existing_module'
  dpc_new_module_name?: string
  dpc_existing_module_name?: string
}
export interface MenuTexts { dpc_plugin_title?: string; dpc_plugin_desc?: string }
export interface DashboardTexts { dpc_plugin_title?: string }

export interface Step3Data {
  /** `<locale>` → { dpc_plugin_title } */
  texts: Record<string, DashboardTexts>
  /** Icone du plugin (classe `fa-...`). */
  icon: string
  /** `<numero d'onglet>` → classe `fa-...` */
  tabIcons: Record<string, string>
}

export interface WizardState {
  step1: Step1Data
  step2: Record<string, MenuTexts>
  step3: Step3Data
  thumbnail: string | null
  /** Derniere etape enregistree (0..3) → reprise apres rechargement. */
  completedStep: number
}

/** `{ champ: { label, messages[] } }`, eventuellement imbrique (par langue). */
export type FieldErrors = Record<string, { label: string; messages: string[] }>
export type StepErrors = Record<string, unknown>
export interface StepResult { valid: boolean; errors: StepErrors }

export interface Summary extends WizardState {
  languages: Language[]
  targetModule: string | null
  existingModule: string | null
  isInactiveExistingModule: number
}

export interface GenerateResult {
  generated: boolean
  module: string
  plugin: string
  /** L'utilisateur a coche « activer le plugin » ⇒ la plateforme doit etre rechargee. */
  restartRequired: boolean
  /** Avertissements NON bloquants. */
  notices: string[]
}

// ─── Endpoints ───────────────────────────────────────────────────────────────

export const fetchContext = () => apiFetch<Context>('/context')
export const fetchState = () => apiFetch<WizardState>('/state')
export const resetWizard = () => postJson<WizardState>('/reset', {})

export const saveStep1 = (d: Step1Data) => postJson<StepResult>('/step/1', d)
export const saveStep2 = (languages: Record<string, MenuTexts>) => postJson<StepResult>('/step/2', { languages })
export const saveStep3 = (languages: Record<string, DashboardTexts>, icon: Record<string, string>) =>
  postJson<StepResult>('/step/3', { languages, icon })

export const fetchSummary = () => apiFetch<Summary>('/summary')

export const generatePlugin = (activate: boolean) =>
  postJson<GenerateResult>('/generate', { dpc_activate_plugin: activate })

export async function uploadThumbnail(file: File): Promise<string> {
  const fd = new FormData()
  fd.append('dpc_plugin_upload_thumbnail', file)
  const data = await apiFetch<{ thumbnail: string }>('/thumbnail', { method: 'POST', body: fd })
  return data.thumbnail
}

export const removeThumbnail = () => postJson<{ thumbnail: null }>('/thumbnail/remove', {})
