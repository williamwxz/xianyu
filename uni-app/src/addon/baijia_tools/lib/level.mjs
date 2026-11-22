// Growth bar for the 我的 page, from niucloud's member/level list
// (each level: { level_id, level_name, growth, level_benefits }).

// currentId = member.member_level (the level niucloud actually assigned).
export function levelProgress(levels, growth, currentId) {
  const list = (levels || []).filter((l) => l && l.level_name).slice().sort((a, b) => Number(a.growth || 0) - Number(b.growth || 0))
  const g = Math.max(0, Number(growth) || 0)
  if (!list.length) return null
  let i = list.findIndex((l) => currentId !== undefined && currentId !== null && String(l.level_id) === String(currentId))
  if (i < 0) {
    // No assigned level: highest threshold reached, or -1 if not even the first.
    i = -1
    for (let k = 0; k < list.length; k++) if (g >= Number(list[k].growth || 0)) i = k
  }
  const next = list[i + 1] || null
  let percent = 100
  if (i < 0) percent = 0
  else if (list.length > 1 && next) {
    const from = Number(list[i].growth || 0)
    const span = Number(next.growth) - from
    const part = span > 0 ? Math.min(1, Math.max(0, (g - from) / span)) : 0
    percent = Math.round(((i + part) / (list.length - 1)) * 100)
  }
  return {
    nodes: list.map((l, k) => ({ id: l.level_id, name: l.level_name, reached: k <= i, current: k === i, left: list.length > 1 ? Math.round((k / (list.length - 1)) * 100) : 0 })),
    percent,
    growth: g,
    next: next ? { name: next.level_name, gap: Math.max(0, Number(next.growth) - g) } : null
  }
}

// niucloud's MemberLevelService pads every level up to 4 perks with these
// placeholder keys (专属客服 / 专属徽章 / ...); only show what the admin configured.
const FILLER_KEYS = ['benefits_one', 'benefits_two', 'benefits_three', 'benefits_four']

// Admin-configured perks of a level: [{ title, icon }].
export function levelPerks(level) {
  if (!level || !level.level_benefits) return []
  return Object.entries(level.level_benefits)
    .filter(([key]) => !FILLER_KEYS.includes(key))
    .map(([, b]) => b && b.content)
    .filter((c) => c && c.title)
    .map((c) => ({ title: c.title, icon: c.icon || '' }))
}
