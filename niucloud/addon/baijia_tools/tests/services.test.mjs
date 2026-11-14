import test from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { SERVICES, HOME_GRID, BRANDS, getService, isReady, resolveLink, searchServices, servicePageUrl } from '../uni-app/lib/services.mjs'

test('service ids are unique and grids only reference known services', () => {
  const ids = SERVICES.map((item) => item.id)
  assert.equal(new Set(ids).size, ids.length)
  for (const id of [...HOME_GRID, ...BRANDS]) assert.ok(getService(id), id)
  assert.equal(BRANDS.length, 10)
})
test('every jtk key used by the app exists in the server-side mapping', () => {
  const dict = readFileSync(new URL('../app/dict/JtkActDict.php', import.meta.url), 'utf8')
  for (const item of SERVICES.filter((s) => s.jtk)) assert.ok(dict.includes(`'${item.jtk}' =>`), item.jtk)
})
test('static launch config is validated', () => {
  assert.equal(isReady({ launch: { type: 'miniProgram', appId: 'wx123' } }), true)
  assert.equal(isReady({ launch: { type: 'webview', url: '' } }), false)
  assert.equal(isReady({ launch: { type: 'unknown', url: 'x' } }), false)
  assert.equal(isReady(null), false)
})
test('link results prefer the mini program, then h5', () => {
  const both = { weapp: { app_id: 'wxabc', page_path: '/p?a=1' }, h5: 'https://s.example/x' }
  assert.deepEqual(resolveLink(both, true), { type: 'miniProgram', appId: 'wxabc', path: '/p?a=1' })
  assert.deepEqual(resolveLink(both, false), { type: 'redirect', url: 'https://s.example/x' })
  assert.deepEqual(resolveLink({ weapp: null, h5: 'https://s.example/y' }, true), { type: 'copy', url: 'https://s.example/y' })
  assert.equal(resolveLink({ weapp: null, h5: '' }, true), null)
  assert.equal(resolveLink(null, true), null)
})
test('search matches names and slogans, ignores blank input', () => {
  assert.deepEqual(searchServices('电影').map((item) => item.id), ['movie'])
  assert.ok(searchServices('团购').some((item) => item.id === 'daodian'))
  assert.deepEqual(searchServices('   '), [])
  assert.equal(getService('nope'), null)
})
test('service page url is encoded and every new page is registered', () => {
  assert.equal(servicePageUrl('a b'), '/addon/baijia_tools/pages/service/index?id=a%20b')
  const pages = readFileSync(new URL('../package/uni-app-pages.php', import.meta.url), 'utf8')
  for (const page of ['home', 'groupbuy', 'express', 'mine', 'service']) assert.ok(pages.includes(`pages/${page}/index`), page)
})
