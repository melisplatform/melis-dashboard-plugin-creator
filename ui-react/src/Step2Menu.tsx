import { useRef, useState } from 'react'
import type { Context, MenuTexts, StepErrors } from './dpc-api'
import { removeThumbnail, uploadThumbnail } from './dpc-api'
import {
  Field, Notice, SpinIcon, TrashIcon, btnGhost, card, formatBytes, hint, inputCss, LangTabs,
  notify, textareaCss, useT,
} from './ui'

/**
 * Etape 2 — titre/description par langue (menu deroulant de droite) + vignette du plugin.
 *
 * Deux capacites distinctes (cf. config/react.capabilities.php) : `wizard.edit` pour les textes,
 * `thumbnail.create` / `thumbnail.delete` pour la vignette. Les boutons masques ici sont AUSSI
 * refuses cote serveur (denyUnlessCan) : le masquage est du confort, pas la securite.
 *
 * L'onglet de langue actif est un etat LOCAL : les volets restent montes, changer de langue ne
 * perd aucune saisie (le parent garde `value`).
 */
export default function Step2Menu({ ctx, value, onChange, thumbnail, onThumbnail, errors, readOnly, canUpload, canDelete }: {
  ctx: Context
  value: Record<string, MenuTexts>
  onChange: (v: Record<string, MenuTexts>) => void
  thumbnail: string | null
  onThumbnail: (t: string | null) => void
  errors: StepErrors
  readOnly: boolean
  canUpload: boolean
  canDelete: boolean
}) {
  const t = useT()
  const [lang, setLang] = useState(ctx.languages[0]?.locale ?? 'en_EN')
  const [busy, setBusy] = useState(false)
  const fileRef = useRef<HTMLInputElement>(null)

  // `errors` est soit { <locale>: {champ:…} } (aucune langue valide), soit { dpc_plugin_upload_thumbnail: … }.
  const langErrors = (errors[lang] ?? {}) as Record<string, { label: string; messages: string[] }>
  const thumbError = errors.dpc_plugin_upload_thumbnail as { messages: string[] } | undefined

  const set = (k: keyof MenuTexts, v: string) =>
    onChange({ ...value, [lang]: { ...(value[lang] ?? {}), [k]: v } })

  async function pick(file: File | undefined) {
    if (!file) return
    setBusy(true)
    try {
      onThumbnail(await uploadThumbnail(file))
    } catch (e) {
      notify('ko', t('s2_thumb'), e instanceof Error ? e.message : String(e))
    } finally {
      setBusy(false)
      if (fileRef.current) fileRef.current.value = ''   // re-choisir le meme fichier doit re-declencher `change`
    }
  }

  async function drop() {
    setBusy(true)
    try {
      await removeThumbnail()
      onThumbnail(null)
    } catch (e) {
      notify('ko', t('s2_thumb'), e instanceof Error ? e.message : String(e))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 720 }}>
      <p style={{ margin: 0, fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('s2_desc')}</p>

      <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
        <LangTabs langs={ctx.languages} active={lang} onChange={setLang}
                  filled={(l) => !!value[l]?.dpc_plugin_title?.trim()} />

        <Field label={t('s2_title_f')} required error={langErrors.dpc_plugin_title?.messages}>
          <input style={inputCss} value={value[lang]?.dpc_plugin_title ?? ''} disabled={readOnly}
                 onChange={(e) => set('dpc_plugin_title', e.target.value)} />
        </Field>
        <Field label={t('s2_desc_f')} error={langErrors.dpc_plugin_desc?.messages}>
          <textarea style={textareaCss} value={value[lang]?.dpc_plugin_desc ?? ''} disabled={readOnly}
                    onChange={(e) => set('dpc_plugin_desc', e.target.value)} />
        </Field>
      </div>

      <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 12 }}>
        <div>
          <span style={{ fontSize: 13, fontWeight: 500 }}>
            {t('s2_thumb')}<span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>
          </span>
          <p style={hint}>{t('s2_thumb_hint', { max: formatBytes(ctx.thumbnail.maxSize) })}</p>
        </div>

        {thumbnail ? (
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <img src={thumbnail} alt="" style={{ width: 190, height: 100, objectFit: 'cover', borderRadius: 8, border: '1px solid var(--color-border)' }} />
            {canDelete && !readOnly && (
              <button type="button" style={{ ...btnGhost, color: 'var(--color-destructive,#ef4444)' }} onClick={drop} disabled={busy}>
                {busy ? <SpinIcon /> : <TrashIcon />}{t('s2_thumb_remove')}
              </button>
            )}
          </div>
        ) : (
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <input ref={fileRef} type="file" accept={ctx.thumbnail.accept} style={{ display: 'none' }}
                   onChange={(e) => pick(e.target.files?.[0])} />
            <button type="button" style={btnGhost} disabled={!canUpload || readOnly || busy}
                    onClick={() => fileRef.current?.click()}>
              {busy && <SpinIcon />}{t('s2_thumb_pick')}
            </button>
            {!canUpload && <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('no_access')}</span>}
          </div>
        )}

        {thumbError && <Notice tone="warn">{t('s2_thumb_missing')}</Notice>}
      </div>
    </div>
  )
}
