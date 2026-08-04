import { useState } from 'react'
import type { Context, DashboardTexts, Step1Data, StepErrors } from './dpc-api'
import { Field, IconGrid, LangTabs, card, inputCss, useT } from './ui'

/**
 * Etape 3 — titre affiche sur la carte du dashboard (par langue) + icone du plugin + icone de
 * chaque onglet (uniquement si le type « multi-onglets » a ete choisi a l'etape 1).
 *
 * Valeurs stockees (identiques au legacy, car ce sont elles que le generateur ecrit) :
 *  - icone du plugin : classe `fa-...` → config du plugin (`'icon' => 'fa fa-calendar'`) ;
 *  - icone d'onglet  : classe GLYPHICONS (`calendar`, `cogwheel`, `chat`…) → la vue generee rend
 *    `<a class="glyphicons <valeur>">…<i></i></a>`. Y mettre `fa-...` ne produirait aucun glyphe.
 * Dans les deux cas le selecteur affiche un SVG inline + le nom lisible (derives de la classe
 * `fa-...` d'apercu fournie par le contexte), car le shell React n'embarque pas FontAwesome.
 *
 * Regle metier reprise du legacy : un titre est valide des qu'UNE langue le renseigne → pastille
 * verte sur l'onglet de langue complet.
 */
export default function Step3Dashboard({ ctx, texts, onTexts, icon, onIcon, tabIcons, onTabIcons, step1, errors, readOnly }: {
  ctx: Context
  texts: Record<string, DashboardTexts>
  onTexts: (v: Record<string, DashboardTexts>) => void
  icon: string
  onIcon: (v: string) => void
  tabIcons: Record<string, string>
  onTabIcons: (v: Record<string, string>) => void
  step1: Step1Data
  errors: StepErrors
  readOnly: boolean
}) {
  const t = useT()
  const [lang, setLang] = useState(ctx.languages[0]?.locale ?? 'en_EN')

  const isMulti  = step1.dpc_plugin_type === 'multi'
  const tabCount = isMulti ? Math.max(0, parseInt(step1.dpc_tab_count ?? '0', 10) || 0) : 0

  // errors : { <locale>: { dpc_plugin_title: {messages} } } et/ou { icon: { dpc_plugin_icon…, dpc_plugin_tab_icon_N… } }
  const langErrors = (errors[lang] ?? {}) as Record<string, { messages: string[] }>
  const iconErrors = (errors.icon ?? {}) as Record<string, { messages: string[] }>

  const setTitle = (v: string) =>
    onTexts({ ...texts, [lang]: { ...(texts[lang] ?? {}), dpc_plugin_title: v } })
  const setTabIcon = (i: number, v: string) => onTabIcons({ ...tabIcons, [String(i)]: v })

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 860 }}>
      <p style={{ margin: 0, fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('s3_desc')}</p>

      {/* Titre du dashboard, par langue */}
      <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
        <LangTabs langs={ctx.languages} active={lang} onChange={setLang}
                  filled={(l) => !!texts[l]?.dpc_plugin_title?.trim()} />
        <Field label={t('s3_title_f')} required error={langErrors.dpc_plugin_title?.messages}>
          <input style={inputCss} value={texts[lang]?.dpc_plugin_title ?? ''} disabled={readOnly}
                 onChange={(e) => setTitle(e.target.value)} />
        </Field>
      </div>

      {/* Icone du plugin */}
      <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 12 }}>
        <div>
          <span style={{ fontSize: 13, fontWeight: 500 }}>
            {t('s3_icon')}<span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>
          </span>
          <p style={{ marginTop: 4, fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('s3_icon_hint')}</p>
        </div>
        <IconGrid icons={ctx.pluginIcons} value={icon} onChange={onIcon} disabled={readOnly} />
        {iconErrors.dpc_plugin_icon?.messages.map((m, i) => (
          <p key={i} style={{ fontSize: 12, color: 'var(--color-destructive,#ef4444)' }}>{m}</p>
        ))}
      </div>

      {/* Icones des onglets (multi-onglets uniquement) */}
      {isMulti && tabCount > 0 && (
        <div style={{ ...card, padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
          <span style={{ fontSize: 13, fontWeight: 600 }}>{t('s3_tab_icons')}</span>
          {Array.from({ length: tabCount }, (_, idx) => {
            const n = idx + 1
            const err = iconErrors[`dpc_plugin_tab_icon_${n}`]?.messages
            return (
              <div key={n} style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                <span style={{ fontSize: 13, fontWeight: 500 }}>
                  {t('s3_tab', { n })}<span style={{ color: 'var(--color-destructive,#ef4444)', marginLeft: 3 }}>*</span>
                </span>
                <IconGrid icons={ctx.tabIcons} value={tabIcons[String(n)] ?? ''} onChange={(v) => setTabIcon(n, v)} disabled={readOnly} />
                {err?.map((m, i) => <p key={i} style={{ fontSize: 12, color: 'var(--color-destructive,#ef4444)' }}>{m}</p>)}
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}
