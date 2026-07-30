import type { Context, FieldErrors, Step1Data } from './dpc-api'
import { Field, Notice, card, inputCss, useT } from './ui'

/**
 * Etape 1 — nom du plugin, type d'affichage (simple / multi-onglets), nombre d'onglets, et
 * destination (nouveau module / module existant). Les regles metier (mot reserve PHP, module deja
 * existant, plugin deja declare) sont validees PAR LE SERVEUR : ici on n'affiche que les messages
 * renvoyes, on ne les duplique pas.
 */
export default function Step1Plugin({ ctx, value, onChange, errors, readOnly }: {
  ctx: Context
  value: Step1Data
  onChange: (v: Step1Data) => void
  errors: FieldErrors
  readOnly: boolean
}) {
  const t = useT()
  const set = (k: keyof Step1Data, v: string) => onChange({ ...value, [k]: v })
  const type = value.dpc_plugin_type ?? ''
  const dest = value.dpc_plugin_destination ?? ''
  const noModules = ctx.siteModules.length === 0

  const optionCard = (checked: boolean, off: boolean) => ({
    display: 'flex', alignItems: 'flex-start', gap: 10, padding: 12, borderRadius: 8,
    cursor: off ? 'not-allowed' : 'pointer', flex: 1, opacity: off ? 0.55 : 1,
    border: `1px solid ${checked ? 'var(--color-primary)' : 'var(--color-border)'}`,
    background: checked ? 'color-mix(in srgb, var(--color-primary) 6%, transparent)' : 'transparent',
  } as const)

  const radio = (name: string, current: string, v: string, lbl: string, onPick: () => void, note?: string, off?: boolean) => (
    <label style={optionCard(current === v, !!off)}>
      <input type="radio" name={name} checked={current === v} disabled={off || readOnly}
             onChange={onPick} style={{ marginTop: 2 }} />
      <span>
        <span style={{ display: 'block', fontSize: 14, fontWeight: 500 }}>{lbl}</span>
        {note && <span style={{ display: 'block', fontSize: 12, color: 'var(--color-muted-foreground)', marginTop: 2 }}>{note}</span>}
      </span>
    </label>
  )

  return (
    <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 18, maxWidth: 720 }}>
      <Field label={t('s1_name')} required error={errors.dpc_plugin_name?.messages}>
        <input style={inputCss} value={value.dpc_plugin_name ?? ''} disabled={readOnly}
               onChange={(e) => set('dpc_plugin_name', e.target.value)} placeholder="SalesOverview" />
      </Field>

      <div>
        <label style={{ display: 'block', fontSize: 13, fontWeight: 500, marginBottom: 6 }}>
          {t('s1_type')}<span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>
        </label>
        <div style={{ display: 'flex', gap: 10 }}>
          {radio('dpc_plugin_type', type, 'single', t('s1_single'), () => set('dpc_plugin_type', 'single'))}
          {radio('dpc_plugin_type', type, 'multi', t('s1_multi'), () => set('dpc_plugin_type', 'multi'))}
        </div>
        {errors.dpc_plugin_type?.messages.map((m, i) => (
          <p key={i} style={{ marginTop: 4, fontSize: 12, color: 'var(--color-destructive,#ef4444)' }}>{m}</p>
        ))}
      </div>

      {type === 'multi' && (
        <div style={{ maxWidth: 240 }}>
          <Field label={t('s1_tab_count')} required hint={t('s1_tab_count_hint', { min: ctx.minTabs, max: ctx.maxTabs })}
                 error={errors.dpc_tab_count?.messages}>
            <input type="number" min={ctx.minTabs} max={ctx.maxTabs} style={inputCss} value={value.dpc_tab_count ?? ''}
                   disabled={readOnly} onChange={(e) => set('dpc_tab_count', e.target.value)} />
          </Field>
        </div>
      )}

      <div>
        <label style={{ display: 'block', fontSize: 13, fontWeight: 500, marginBottom: 6 }}>
          {t('s1_dest')}<span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>
        </label>
        <div style={{ display: 'flex', gap: 10 }}>
          {radio('dpc_plugin_destination', dest, 'new_module', t('s1_new'), () => set('dpc_plugin_destination', 'new_module'))}
          {radio('dpc_plugin_destination', dest, 'existing_module', t('s1_existing'),
                 () => set('dpc_plugin_destination', 'existing_module'),
                 noModules ? t('s1_no_modules') : undefined, noModules)}
        </div>
        {errors.dpc_plugin_destination?.messages.map((m, i) => (
          <p key={i} style={{ marginTop: 4, fontSize: 12, color: 'var(--color-destructive,#ef4444)' }}>{m}</p>
        ))}
      </div>

      {dest === 'new_module' && (
        <Field label={t('s1_new_name')} required error={errors.dpc_new_module_name?.messages}>
          <input style={inputCss} value={value.dpc_new_module_name ?? ''} disabled={readOnly}
                 onChange={(e) => set('dpc_new_module_name', e.target.value)} placeholder="MyDashboards" />
        </Field>
      )}

      {dest === 'existing_module' && (
        <Field label={t('s1_existing_name')} required error={errors.dpc_existing_module_name?.messages}>
          <select style={inputCss} value={value.dpc_existing_module_name ?? ''} disabled={readOnly}
                  onChange={(e) => set('dpc_existing_module_name', e.target.value)}>
            <option value="">{t('s1_choose')}</option>
            {ctx.siteModules.map((m) => <option key={m} value={m}>{m}</option>)}
          </select>
        </Field>
      )}

      {readOnly && <Notice tone="warn">{t('readonly_notice')}</Notice>}
    </div>
  )
}
