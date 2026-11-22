// Growth bar for the 我的 page, from niucloud's member/level list
// (each level: { level_id, level_name, growth, level_benefits }).

export function levelProgress(levels, growth) {
  const list = (levels || []).filter((l) => l && l.level_name).slice().sort((a, b) => Number(a.growth || 0) - Number(b.growth || 0))
  const g = Math.max(0, Number(growth) || 0)
  if (!list.length) return null
  let i = 0
  for (let k = 0; k < list.length; k++) if (g >= Number(list[k].growth || 0)) i = k
  const next = list[i + 1] || null
  let percent = 100
  if (list.length > 1 && next) {
    const from = Number(list[i].growth || 0)
    const span = Number(next.growth) - from
    const part = span > 0 ? Math.min(1, (g - from) / span) : 0
    percent = Math.round(((i + part) / (list.length - 1)) * 100)
  }
  return {
    nodes: list.map((l, k) => ({ id: l.level_id, name: l.level_name, reached: k <= i, current: k === i, left: list.length > 1 ? Math.round((k / (list.length - 1)) * 100) : 0 })),
    percent,
    growth: g,
    next: next ? { name: next.level_name, gap: Number(next.growth) - g } : null
  }
}

// Admin-configured perks of a level: [{ title, icon }].
export function levelPerks(level) {
  if (!level || !level.level_benefits) return []
  return Object.values(level.level_benefits)
    .map((b) => b && b.content)
    .filter((c) => c && c.title)
    .map((c) => ({ title: c.title, icon: c.icon || '' }))
}
