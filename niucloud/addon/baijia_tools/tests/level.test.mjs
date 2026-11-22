import test from 'node:test'
import assert from 'node:assert/strict'
import { levelProgress, levelPerks } from '../uni-app/lib/level.mjs'

const levels = [
  { level_id: 2, level_name: '铜牌', growth: 500 },
  { level_id: 1, level_name: '普通会员', growth: 0 },
  { level_id: 3, level_name: '银牌', growth: 2000 },
  { level_id: 4, level_name: '金牌', growth: 5000 }
]

test('progress sits between the current and next level', () => {
  const p = levelProgress(levels, 10)
  assert.deepEqual(p.nodes.map((n) => n.name), ['普通会员', '铜牌', '银牌', '金牌'])
  assert.equal(p.nodes[0].current, true)
  assert.deepEqual(p.next, { name: '铜牌', gap: 490 })
  assert.equal(p.percent, Math.round((10 / 500 / 3) * 100))
})
test('exact threshold reaches the level; top level is 100%', () => {
  assert.equal(levelProgress(levels, 500).nodes[1].current, true)
  const top = levelProgress(levels, 9999)
  assert.equal(top.percent, 100)
  assert.equal(top.next, null)
})
test('empty or bad input', () => {
  assert.equal(levelProgress([], 10), null)
  assert.equal(levelProgress(null, 10), null)
  assert.equal(levelProgress(levels, 'x').growth, 0)
  assert.equal(levelProgress([{ level_id: 1, level_name: 'A', growth: 0 }], 5).percent, 100)
})
test('perks come from configured benefits only', () => {
  const lvl = { level_benefits: { discount: { content: { title: '单单多返1元', icon: 'a.png' } }, empty: { content: null }, x: {} } }
  assert.deepEqual(levelPerks(lvl), [{ title: '单单多返1元', icon: 'a.png' }])
  assert.deepEqual(levelPerks(null), [])
})
