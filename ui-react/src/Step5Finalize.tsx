import { useEffect, useState } from 'react'
import type { GenerateResult } from './dpc-api'
import { fetchSummary, generatePlugin } from './dpc-api'
import { CheckIcon, Notice, SpinIcon, Toggle, btnGhost, btnPrimary, card, hint, useT } from './ui'

/**
 * Etape 5 — finalisation. Le SEUL endroit qui mute la plateforme (ecriture de fichiers PHP,
 * activation du module) → garde par la capacite `finalization` + `finalization.create`, cote
 * serveur ET cote UI. Le bouton n'est meme pas rendu sans le droit.
 *
 * Contrairement au templating, aucun choix de site : un nouveau module est active globalement
 * (`activateModule`) puis le cache du menu du dashboard est vide cote serveur.
 *
 * `restartRequired` (l'utilisateur a demande l'activation) declenche un compte a rebours puis un
 * rechargement complet — la plateforme doit relire ses chemins de modules.
 */
export default function Step5Finalize({ isNewModule, canGenerate, onDone }: {
  isNewModule: boolean
  canGenerate: boolean
  onDone: () => void
}) {
  const t = useT()
  const [activate, setActivate] = useState(true)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [result, setResult] = useState<GenerateResult | null>(null)
  /** Note « le module devra etre active » quand la cible est un module existant inactif. */
  const [inactiveModule, setInactiveModule] = useState<string | null>(null)

  // Best-effort : sert uniquement a afficher la note d'activation du module existant inactif.
  useEffect(() => {
    let alive = true
    fetchSummary()
      .then((s) => { if (alive && !isNewModule && s.isInactiveExistingModule) setInactiveModule(s.existingModule) })
      .catch(() => { /* pas de note si l'acces au recapitulatif est refuse */ })
    return () => { alive = false }
  }, [isNewModule])

  async function run() {
    setBusy(true)
    setError(null)
    try {
      setResult(await generatePlugin(activate))
    } catch (e) {
      setError(e instanceof Error ? e.message : String(e))
    } finally {
      setBusy(false)
    }
  }

  if (result) return <Success result={result} onDone={onDone} />

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 720 }}>
      <p style={{ margin: 0, fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('s5_desc')}</p>

      {!canGenerate && <Notice tone="warn">{t('s5_no_access')}</Notice>}
      {error && <Notice tone="warn"><span dangerouslySetInnerHTML={{ __html: error }} /></Notice>}
      {inactiveModule && <Notice tone="warn">{t('s5_module_note', { m: inactiveModule })}</Notice>}

      <div style={{ ...card, padding: 20 }}>
        <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
          <Toggle on={activate} onClick={() => setActivate((v) => !v)} disabled={!canGenerate || busy} />
          <div>
            <span style={{ fontSize: 14, fontWeight: 500 }}>{t('s5_activate')}</span>
            <p style={hint}>{t('s5_activate_note')}</p>
          </div>
        </div>
      </div>

      {canGenerate && (
        <div>
          <button style={{ ...btnPrimary, height: 40 }} onClick={run} disabled={busy}>
            {busy ? <><SpinIcon />{t('s5_generating')}</> : <><CheckIcon />{t('s5_generate')}</>}
          </button>
        </div>
      )}
    </div>
  )
}

/** Ecran de succes : avertissements non bloquants + compte a rebours de rechargement. */
function Success({ result, onDone }: { result: GenerateResult; onDone: () => void }) {
  const t = useT()
  const [left, setLeft] = useState(5)

  useEffect(() => {
    if (!result.restartRequired) return
    if (left <= 0) { window.location.reload(); return }
    const id = window.setTimeout(() => setLeft((n) => n - 1), 1000)
    return () => window.clearTimeout(id)
  }, [result.restartRequired, left])

  return (
    <div style={{ ...card, padding: 28, display: 'flex', flexDirection: 'column', gap: 14, alignItems: 'flex-start', maxWidth: 720 }}>
      <div style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 44, height: 44, borderRadius: '50%', background: 'color-mix(in srgb, #22c55e 18%, transparent)', color: '#16a34a' }}>
        <CheckIcon />
      </div>
      <h2 style={{ margin: 0, fontSize: 18, fontWeight: 700 }}>{t('ok_title')}</h2>
      <div style={{ fontSize: 14, color: 'var(--color-muted-foreground)' }}>
        <div>{t('ok_module', { m: result.module })}</div>
        <div>{t('ok_plugin', { p: result.plugin })}</div>
      </div>

      {result.notices.map((n, i) => <Notice key={i} tone="warn"><span dangerouslySetInnerHTML={{ __html: n }} /></Notice>)}

      {result.restartRequired
        ? <p style={{ margin: 0, fontSize: 14 }}>{t('ok_reload', { n: left })}</p>
        : <p style={{ margin: 0, fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('ok_manual')}</p>}

      <button style={btnGhost} onClick={onDone}>{t('ok_new')}</button>
    </div>
  )
}
