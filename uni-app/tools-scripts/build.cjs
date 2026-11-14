const path = require('node:path')
const fs = require('node:fs')
const { spawnSync } = require('node:child_process')
const platform = process.argv[2]
if (!['mp-weixin', 'h5'].includes(platform)) throw new Error('Expected mp-weixin or h5')
const root = path.resolve(__dirname, '..')
const input = path.join(root, 'tools-src')
const output = path.join(root, 'dist/tools', platform)
const result = spawnSync(process.execPath, [
  path.join(root, 'node_modules/@dcloudio/vite-plugin-uni/bin/uni.js'),
  'build', '-p', platform, '--config', 'vite.tools.config.ts'
], { cwd: root, stdio: 'inherit', env: { ...process.env, UNI_INPUT_DIR: input, UNI_OUTPUT_DIR: output } })
if (result.error) throw result.error
if (result.status !== 0) process.exit(result.status || 1)
const pages = JSON.parse(fs.readFileSync(path.join(input, 'pages.json'), 'utf8'))
for (const tab of pages.tabBar.list) {
  for (const key of ['iconPath', 'selectedIconPath']) {
    if (!fs.existsSync(path.join(output, tab[key]))) throw new Error(`Missing built tab icon: ${tab[key]}`)
  }
}
console.log(`Verified tab assets: ${output}`)
