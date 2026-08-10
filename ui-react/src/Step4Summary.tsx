import { useEffect, useState, type ReactNode } from 'react'
import type { Summary } from './dpc-api'
import { fetchSummary } from './dpc-api'
import { FaIcon, Notice, SpinIcon, card, iconLabel, td, useT } from './ui'

/**
 * Etape 4 — recapitulatif en lecture seule (etapes 1 → 3), lu depuis `GET /dpc/summary`.
 * Endpoint garde par les capacites `summary` + `summary.list` : si l'utilisateur ne les a pas,
 * l'etape est retiree du parcours par le parent — ce composant n'est alors jamais monte.
 *
 * Recharge a chaque ENTREE dans l'etape (`active`), pas a chaque rendu.
 *
 * `tabIconPreview` : les icones d'onglet sont stockees en classes Glyphicons ; l'apercu passe par
 * la classe `fa-...` equivalente fournie par le contexte (cf. IconOption).
 */
export default function Step4Summary({ active, tabIconPreview = {} }: {
  active: boolean
  tabIconPreview?: Record<string, string>
}) {
  const t = useT()
  const [data, setData] = useState<Summary | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (!active) return
    let alive = true
    setLoading(true)
    fetchSummary()
      .then((d) => { if (alive) { setData(d); setError(null) } })
      .catch((e: Error) => { if (alive) setError(e.message) })
      .finally(() => { if (alive) setLoading(false) })
    return () => { alive = false }
  }, [active])

  if (loading && !data) return <div style={{ ...card, padding: 24, display: 'flex', gap: 8, alignItems: 'center', maxWidth: 720 }}><SpinIcon />{t('loading')}</div>
  if (error) return <Notice tone="warn">{error}</Notice>
  if (!data) return null

  const langName = (locale: string) => data.languages.find((l) => l.locale === locale)?.name ?? locale
  const isMulti = data.step1.dpc_plugin_type === 'multi'
  const tabIcons = data.step3.tabIcons ?? {}

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16, maxWidth: 860 }}>
      <p style={{ margin: 0, fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('s4_desc')}</p>

      <div style={{ ...card, padding: 20, display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 16 }}>
        <Kv label={t('s4_plugin')} value={data.step1.dpc_plugin_name ?? '—'} />
        <Kv label={t('s4_target')} value={data.targetModule ?? '—'} />
        <Kv label={t('s4_type')} value={isMulti ? `${t('s1_multi')} · ${data.step1.dpc_tab_count ?? ''} ${t('s4_tabs')}` : t('s1_single')} />
      </div>

      {data.thumbnail && (
        <div style={{ ...card, padding: 20 }}>
          <img src={data.thumbnail} alt="" style={{ width: 190, height: 100, objectFit: 'cover', borderRadius: 8, border: '1px solid var(--color-border)' }} />
        </div>
      )}

      <Section title={t('s4_menu')}>
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
          <tbody>
            {Object.entries(data.step2).map(([locale, texts]) => (
              <tr key={locale}>
                <td style={{ ...td, width: 140, color: 'var(--color-muted-foreground)' }}>{langName(locale)}</td>
                <td style={td}>
                  <strong>{texts.dpc_plugin_title}</strong>
                  {texts.dpc_plugin_desc && <div style={{ color: 'var(--color-muted-foreground)', marginTop: 2 }}>{texts.dpc_plugin_desc}</div>}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Section>

      <Section title={t('s4_dashboard')}>
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
          <tbody>
            {Object.entries(data.step3.texts).map(([locale, texts]) => (
              <tr key={locale}>
                <td style={{ ...td, width: 140, color: 'var(--color-muted-foreground)' }}>{langName(locale)}</td>
                <td style={td}>{texts.dpc_plugin_title}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </Section>

      <Section title={t('s4_icon')}>
        <div style={{ padding: 14, display: 'flex', alignItems: 'center', gap: 10 }}>
          {data.step3.icon ? <><FaIcon icon={data.step3.icon} size={22} /><span style={{ fontSize: 14 }}>{iconLabel(data.step3.icon)}</span></> : '—'}
        </div>
      </Section>

      {isMulti && Object.keys(tabIcons).length > 0 && (
        <Section title={t('s4_tab_icons')}>
          <div style={{ padding: 14, display: 'flex', flexWrap: 'wrap', gap: 16 }}>
            {Object.entries(tabIcons).sort((a, b) => Number(a[0]) - Number(b[0])).map(([n, ic]) => {
              const preview = tabIconPreview[ic] ?? ic
              return (
                <div key={n} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{t('s3_tab', { n })}</span>
                  <FaIcon icon={preview} size={20} />
                  <span style={{ fontSize: 13 }}>{iconLabel(preview)}</span>
                </div>
              )
            })}
          </div>
        </Section>
      )}
    </div>
  )
}

function Kv({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <span style={{ display: 'block', fontSize: 12, color: 'var(--color-muted-foreground)' }}>{label}</span>
      <span style={{ fontSize: 14, fontWeight: 500, wordBreak: 'break-all' }}>{value}</span>
    </div>
  )
}

function Section({ title, children }: { title: string; children: ReactNode }) {
  return (
    <div style={{ ...card, overflow: 'hidden' }}>
      <div style={{ padding: '10px 14px', borderBottom: '1px solid var(--color-border)', fontSize: 13, fontWeight: 600 }}>{title}</div>
      {children}
    </div>
  )
}
