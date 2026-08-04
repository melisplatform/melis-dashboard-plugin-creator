import { useCallback, useEffect, useState, type ReactNode } from 'react'
import Step1Plugin from './Step1Plugin'
import Step2Menu from './Step2Menu'
import Step3Dashboard from './Step3Dashboard'
import Step4Summary from './Step4Summary'
import Step5Finalize from './Step5Finalize'
import { ViewToggle, type ViewMode } from './ViewToggle'
import { useCaps } from './shared/useCaps'
import {
  ArrowLeft, ArrowRight, BrickStyles, CheckIcon, ErrorBanner, Notice, Pane, RotateIcon, SpinIcon,
  btnGhost, btnPrimary, card, useT,
} from './ui'
import type {
  Context, DashboardTexts, FieldErrors, MenuTexts, Step1Data, StepErrors, StepResult, WizardState,
} from './dpc-api'
import {
  fetchContext, fetchState, resetWizard, saveStep1, saveStep2, saveStep3,
} from './dpc-api'

/* ──────────────────────────────────────────────────────────────────────────────
 * Brique « Dashboard Plugin Creator » (MelisDashboardPluginCreator) — full React.
 *
 * L'outil legacy est un ASSISTANT en 5 etapes qui genere du code. On le rebatit en React natif
 * (formulaires, navigation, recapitulatif), mais TOUTE la logique metier reste cote Melis :
 * validation par les formulaires Laminas d'origine, generation par MelisDashboardPluginCreatorService.
 * Cf. src/Controller/MelisReactApiDashboardPluginCreatorController.php.
 *
 * ── Pas de rechargement intempestif ────────────────────────────────────────────
 *  1. La brique est `persistent` (brick.manifest.json) : l'hote la monte une fois et ne la demonte
 *     plus → quitter l'onglet de l'outil puis y revenir ne perd NI la saisie NI l'etape courante.
 *  2. Les 5 etapes sont des volets montes UNE fois puis caches en CSS (`<Pane>`), pas un composant
 *     echange selon l'etape : revenir en arriere ne remonte rien, ne refetch rien, ne perd rien.
 *  3. L'iframe « Old » n'est montee qu'au premier passage sur la vue legacy, puis gardee en
 *     `display:none` → basculer New/Old ne la recharge jamais.
 *
 * ── Droits ─────────────────────────────────────────────────────────────────────
 * Les capacites (`config/react.capabilities.php`) masquent ici les controles ; le serveur les
 * REFUSE de son cote (denyUnlessCan sur l'onglet ET l'action). Masquer n'est jamais la securite.
 * ────────────────────────────────────────────────────────────────────────────── */

const MELIS_KEY = 'melisdashboardplugincreator_tool'
const STEP_COUNT = 5

export default function DpcPage() {
  const t = useT()
  const { can } = useCaps(MELIS_KEY)   // droits avances (config/react.capabilities.php)

  const [ctx, setCtx] = useState<Context | null>(null)
  const [fatal, setFatal] = useState<string | null>(null)
  const [step, setStep] = useState(1)
  const [busy, setBusy] = useState(false)

  // ── Donnees de l'assistant (une seule source de verite, partagee par les volets) ──
  const [step1, setStep1] = useState<Step1Data>({})
  const [step2, setStep2] = useState<Record<string, MenuTexts>>({})
  const [step3, setStep3] = useState<Record<string, DashboardTexts>>({})
  const [icon, setIcon] = useState('')
  const [tabIcons, setTabIcons] = useState<Record<string, string>>({})
  const [thumbnail, setThumbnail] = useState<string | null>(null)

  const [errors, setErrors] = useState<Record<number, StepErrors>>({})

  const [mode, setMode] = useState<ViewMode>('react')
  const [frameLoaded, setFrameLoaded] = useState(false)

  const canEdit = can('wizard') && can('wizard.edit')
  const canSummary = can('summary') && can('summary.list')
  const canGenerate = can('finalization') && can('finalization.create')
  const readOnly = !canEdit
  const isNewModule = step1.dpc_plugin_destination === 'new_module'

  /** Applique un etat serveur (montage, reset, retour de la vue legacy). */
  const applyState = useCallback((s: WizardState) => {
    setStep1(s.step1 ?? {})
    setStep2(s.step2 ?? {})
    setStep3(s.step3?.texts ?? {})
    setIcon(s.step3?.icon ?? '')
    setTabIcons(s.step3?.tabIcons ?? {})
    setThumbnail(s.thumbnail ?? null)
    setErrors({})
    setStep(Math.min(STEP_COUNT, (s.completedStep ?? 0) + 1))
  }, [])

  // Chargement initial — UNE fois. La brique etant persistante, cela ne se rejoue jamais.
  useEffect(() => {
    let alive = true
    Promise.all([fetchContext(), fetchState()])
      .then(([c, s]) => { if (!alive) return; setCtx(c); applyState(s) })
      .catch((e: Error) => { if (alive) setFatal(e.message) })
    return () => { alive = false }
  }, [applyState])

  /** Valide + enregistre l'etape courante cote serveur, puis avance. */
  async function next() {
    if (step >= STEP_COUNT) return
    if (readOnly || step === 4) { setStep(step + 1); return }

    setBusy(true)
    try {
      let res: StepResult
      switch (step) {
        case 1: res = await saveStep1(step1); break
        case 2: res = await saveStep2(step2); break
        case 3: res = await saveStep3(step3, buildIconPayload()); break
        default: res = { valid: true, errors: {} }
      }
      setErrors((p) => ({ ...p, [step]: res.errors }))
      if (res.valid) setStep(step + 1)
    } catch (e) {
      // Erreur technique OU refus de droits (403) → banniere, pas d'avancement.
      setErrors((p) => ({ ...p, [step]: { __http: { label: '', messages: [(e as Error).message] } } }))
    } finally {
      setBusy(false)
    }
  }

  /** { dpc_plugin_icon, dpc_plugin_tab_icon_1..n } — forme attendue par le formulaire legacy. */
  function buildIconPayload(): Record<string, string> {
    const payload: Record<string, string> = { dpc_plugin_icon: icon }
    const count = step1.dpc_plugin_type === 'multi' ? Math.max(0, parseInt(step1.dpc_tab_count ?? '0', 10) || 0) : 0
    for (let i = 1; i <= count; i++) payload[`dpc_plugin_tab_icon_${i}`] = tabIcons[String(i)] ?? ''
    return payload
  }

  async function restart() {
    setBusy(true)
    try { applyState(await resetWizard()) } catch (e) { setFatal((e as Error).message) } finally { setBusy(false) }
  }

  /**
   * La vue « Old » ouvre le tool legacy, dont `renderToolAction()` VIDE le conteneur de session
   * partage — le brouillon React est donc perdu. On le dit avant de basculer, et au retour on
   * relit l'etat serveur pour rester fidele a la verite (l'assistant repart de l'etape 1).
   */
  async function switchMode(m: ViewMode) {
    if (m === 'iframe' && !frameLoaded) {
      const draft = !!(step1.dpc_plugin_name || thumbnail || Object.keys(step2).length)
      if (draft && !window.confirm(`${t('title')}\n\n${t('old_resets')}`)) return
      setFrameLoaded(true)
    }
    if (m === 'react' && frameLoaded) {
      try { applyState(await fetchState()) } catch { /* on garde l'etat courant */ }
    }
    setMode(m)
  }

  if (fatal) return <Shell><Notice tone="warn">{fatal}</Notice></Shell>
  if (!ctx) return <Shell><div style={{ display: 'flex', gap: 8, alignItems: 'center' }}><SpinIcon />{t('loading')}</div></Shell>

  if (ctx.blocking.length) {
    return (
      <Shell>
        <div style={{ ...card, padding: 20, maxWidth: 720 }}>
          <h2 style={{ margin: '0 0 6px', fontSize: 16, fontWeight: 700 }}>{t('blocking_title')}</h2>
          <p style={{ margin: '0 0 10px', fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('blocking_desc')}</p>
          <ul style={{ margin: 0, paddingLeft: 18, fontSize: 13 }}>
            {ctx.blocking.map((b, i) => <li key={i} dangerouslySetInnerHTML={{ __html: b }} />)}
          </ul>
        </div>
      </Shell>
    )
  }

  // Icone d'onglet stockee (classe Glyphicons) → classe `fa-...` d'apercu, pour le recapitulatif.
  const tabIconPreview = Object.fromEntries(ctx.tabIcons.map((i) => [i.value, i.preview]))

  const stepErrors = errors[step] ?? {}
  const flatMessages = Object.values(stepErrors)
    .flatMap((v) => (v && typeof v === 'object' && 'messages' in v ? (v as { messages: string[] }).messages : []))

  return (
    <Shell
      toolbar={
        <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
          {canEdit && (
            <button style={btnGhost} onClick={restart} disabled={busy}><RotateIcon />{t('restart')}</button>
          )}
          <ViewToggle mode={mode} onChange={(m) => void switchMode(m)} />
        </div>
      }
    >
      {/* Vue « Old » : outil legacy en iframe, montee une seule fois puis gardee en display:none. */}
      {frameLoaded && (
        <div style={{ ...card, display: mode === 'iframe' ? 'flex' : 'none', flex: 1, minHeight: 620, overflow: 'hidden' }}>
          <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`}
                  style={{ flex: 1, width: '100%', border: 0 }} title={t('title')} />
        </div>
      )}

      <div style={{ display: mode === 'react' ? 'flex' : 'none', flexDirection: 'column', gap: 20 }}>
        <Stepper steps={ctx.steps.map((s) => s.name)} current={step} onGo={(n) => n <= step && setStep(n)} />

        {readOnly && <Notice tone="warn">{t('readonly_notice')}</Notice>}
        <ErrorBanner title={t('errors_title')} messages={flatMessages} />

        {/* Volets montes une fois, montres/caches en CSS → aucun remontage, aucun refetch. */}
        <Pane show={step === 1}>
          <Step1Plugin ctx={ctx} value={step1} onChange={setStep1} readOnly={readOnly}
                       errors={(errors[1] ?? {}) as FieldErrors} />
        </Pane>
        <Pane show={step === 2}>
          <Step2Menu ctx={ctx} value={step2} onChange={setStep2} thumbnail={thumbnail} onThumbnail={setThumbnail}
                     errors={errors[2] ?? {}} readOnly={readOnly}
                     canUpload={can('thumbnail') && can('thumbnail.create')}
                     canDelete={can('thumbnail') && can('thumbnail.delete')} />
        </Pane>
        <Pane show={step === 3}>
          <Step3Dashboard ctx={ctx} texts={step3} onTexts={setStep3} icon={icon} onIcon={setIcon}
                          tabIcons={tabIcons} onTabIcons={setTabIcons} step1={step1}
                          errors={errors[3] ?? {}} readOnly={readOnly} />
        </Pane>
        <Pane show={step === 4}>
          {canSummary
            ? <Step4Summary active={step === 4} tabIconPreview={tabIconPreview} />
            : <Notice tone="warn">{t('s4_no_access')}</Notice>}
        </Pane>
        <Pane show={step === 5}>
          <Step5Finalize isNewModule={isNewModule} canGenerate={canGenerate} onDone={restart} />
        </Pane>

        {/* La navigation de l'etape 5 est portee par son propre bouton « Terminer ». */}
        {step < STEP_COUNT && (
          <div style={{ display: 'flex', gap: 10 }}>
            <button style={{ ...btnGhost, visibility: step > 1 ? 'visible' : 'hidden' }}
                    onClick={() => setStep(step - 1)} disabled={busy}><ArrowLeft />{t('back')}</button>
            <button style={btnPrimary} onClick={() => void next()} disabled={busy}>
              {busy ? <><SpinIcon />{t('saving')}</> : <>{step === 4 ? <CheckIcon /> : null}{t('next')}<ArrowRight /></>}
            </button>
          </div>
        )}
      </div>
    </Shell>
  )
}

/** Conteneur plein-ecran, scrollable — meme gabarit que les autres briques full-React. */
function Shell({ children, toolbar }: { children: ReactNode; toolbar?: ReactNode }) {
  const t = useT()
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 20, padding: 24, height: '100%', boxSizing: 'border-box', overflow: 'auto' }}>
      <BrickStyles />
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16 }}>
        <div>
          <h1 style={{ fontSize: 20, fontWeight: 700, margin: 0 }}>{t('title')}</h1>
          <p style={{ fontSize: 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0' }}>{t('subtitle')}</p>
        </div>
        {toolbar}
      </div>
      {children}
    </div>
  )
}

/** Fil des etapes : pastille numerotee + libelle, cliquable vers les etapes deja atteintes. */
function Stepper({ steps, current, onGo }: { steps: string[]; current: number; onGo: (n: number) => void }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 4, flexWrap: 'wrap' }}>
      {steps.map((name, i) => {
        const n = i + 1
        const done = n < current
        const active = n === current
        return (
          <div key={name} style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
            <button type="button" onClick={() => onGo(n)} disabled={n > current} style={{
              display: 'inline-flex', alignItems: 'center', gap: 8, padding: '6px 12px', borderRadius: 999, border: 0,
              cursor: n <= current ? 'pointer' : 'default', fontSize: 13, fontWeight: active ? 600 : 500,
              background: active ? 'color-mix(in srgb, var(--color-primary) 12%, transparent)' : 'transparent',
              color: active ? 'var(--color-primary)' : done ? 'var(--color-foreground)' : 'var(--color-muted-foreground)',
            }}>
              <span style={{
                display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 22, height: 22, borderRadius: '50%',
                fontSize: 11, fontWeight: 600,
                background: active ? 'var(--color-primary)' : done ? '#22c55e' : 'color-mix(in srgb, var(--color-muted,#888) 20%, transparent)',
                color: active || done ? '#fff' : 'var(--color-muted-foreground)',
              }}>{done ? <CheckIcon /> : n}</span>
              {name}
            </button>
            {n < steps.length && <span style={{ width: 16, height: 1, background: 'var(--color-border)' }} />}
          </div>
        )
      })}
    </div>
  )
}
