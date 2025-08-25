import { promises as fs } from 'fs'
import path from 'path'

type AnyRecord = Record<string, any>

const CACHE_ROOT = path.join(process.cwd(), 'server', 'cache')
const MAX_ITEMS_PER_TYPE = Number(process.env.CACHE_MAX_ITEMS || 2000)

async function ensureDir(dir: string) {
  await fs.mkdir(dir, { recursive: true }).catch(() => {})
}

async function writeJson(filePath: string, data: unknown) {
  const payload = (data === undefined) ? {} : data
  const json = JSON.stringify(payload)
  await fs.writeFile(filePath, json, 'utf-8')
}

async function readJson<T = any>(filePath: string, fallback: T): Promise<T> {
  try {
    const text = await fs.readFile(filePath, 'utf-8')
    return JSON.parse(text)
  } catch {
    return fallback
  }
}

async function limitItems(dir: string, indexPath: string) {
  try {
    const ids: string[] = await readJson(indexPath, [])
    if (ids.length <= MAX_ITEMS_PER_TYPE) return
    const over = ids.length - MAX_ITEMS_PER_TYPE
    // 依據檔案 mtime 由舊到新刪除
    const stats = await Promise.all(
      ids.map(async (id) => ({ id, stat: await fs.stat(path.join(dir, `${id}.json`)).catch(() => null) }))
    )
    const existing = stats.filter((s) => s.stat)
    existing.sort((a, b) => (a.stat!.mtimeMs - b.stat!.mtimeMs))
    const toDelete = existing.slice(0, over)
    const remainIds = new Set(ids)
    await Promise.all(toDelete.map(({ id }) => fs.rm(path.join(dir, `${id}.json`), { force: true })))
    toDelete.forEach(({ id }) => remainIds.delete(id))
    await writeJson(indexPath, Array.from(remainIds))
  } catch {}
}

/**
 * 判斷資料是否為「已發布」狀態。
 * 嘗試從根層 `post.post_status` 或多語物件的 `*.post.post_status` 判斷。
 */
function isPublishedRecord(record: any): boolean {
  try {
    const statuses: string[] = []
    const rootStatus = record?.post?.post_status
    if (typeof rootStatus === 'string') statuses.push(rootStatus)
    if (record && typeof record === 'object') {
      for (const v of Object.values(record)) {
        const s = (v as any)?.post?.post_status
        if (typeof s === 'string') statuses.push(s)
      }
    }
    return statuses.some((s) => s === 'publish')
  } catch {
    return false
  }
}

export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  const { id, type, slug, action } = body as { id?: string; type: string; slug?: string; action?: string }

  console.log(`收到重新驗證請求：${type} ${id ?? ''} ${action || '更新'}`)

  await ensureDir(CACHE_ROOT)

  // global 單檔處理
  if (type === 'global') {
    const globalPath = path.join(CACHE_ROOT, 'global.json')
    if (action === 'delete') {
      await fs.writeFile(globalPath, '{}', 'utf-8').catch(() => {})
      return { success: true }
    }
    const data = await $fetch('/api/save/global')
    await writeJson(globalPath, data)
    return { success: true }
  }

  // 其他 type 使用每筆一檔 + 索引
  if (!id) {
    throw createError({ statusCode: 400, statusMessage: '缺少必要的 id 參數' })
  }

  const typeDir = path.join(CACHE_ROOT, type)
  const indexPath = path.join(typeDir, 'index.json') // string[] of ids
  const slugIndexPath = path.join(typeDir, 'slug-index.json') // { [slug]: id }
  await ensureDir(typeDir)

  if (action === 'delete') {
    const filePath = path.join(typeDir, `${id}.json`)
    await fs.rm(filePath, { force: true })
    const ids: string[] = await readJson(indexPath, [])
    const nextIds = ids.filter((x) => x !== String(id))
    await writeJson(indexPath, nextIds)

    // 清除 slug 對應：若有傳入 slug 僅刪該鍵，否則清除所有指向該 id 的鍵
    const slugIndex: AnyRecord = await readJson(slugIndexPath, {})
    if (slug) {
      if (slugIndex[slug] === String(id)) delete slugIndex[slug]
    } else {
      for (const k of Object.keys(slugIndex)) {
        if (slugIndex[k] === String(id)) delete slugIndex[k]
      }
    }
    await writeJson(slugIndexPath, slugIndex)

    return { success: true }
  }

  // 取得資料並寫入該 id 檔案
  let apiUrl: string
  if (type === 'page') {
    apiUrl = `/api/save/page?id=${id}`
  } else {
    apiUrl = `/api/save/collection?type=${type}&id=${id}`
  }
  const data = await $fetch(apiUrl)

  // 允許 options 類型（如 blog_options/portfolio_options/...）即使沒有 post_status 也可快取
  const isOptionsKey = typeof id === 'string' && /_options$/.test(id)

  // 僅快取已發布內容（非 options）：若未發布，移除現有檔案與索引，並結束
  if (!isOptionsKey && !isPublishedRecord(data)) {
    const filePath = path.join(typeDir, `${id}.json`)
    await fs.rm(filePath, { force: true })

    const ids: string[] = await readJson(indexPath, [])
    const nextIds = ids.filter((x) => x !== String(id))
    if (nextIds.length !== ids.length) {
      await writeJson(indexPath, nextIds)
    }

    const slugIndex: AnyRecord = await readJson(slugIndexPath, {})
    if (slug) {
      if (slugIndex[slug] === String(id)) delete slugIndex[slug]
    } else {
      for (const k of Object.keys(slugIndex)) {
        if (slugIndex[k] === String(id)) delete slugIndex[k]
      }
    }
    await writeJson(slugIndexPath, slugIndex)

    return { success: true, skipped: true, reason: 'not_published' }
  }

  const itemPath = path.join(typeDir, `${id}.json`)
  await writeJson(itemPath, data)

  // 更新索引
  const ids: string[] = await readJson(indexPath, [])
  if (!ids.includes(String(id))) ids.push(String(id))
  await writeJson(indexPath, ids)

  // 更新 slug 索引（若可用）
  try {
    const anyData = data as any
    const postSlug = anyData?.post?.post_name || slug
    if (postSlug) {
      const slugIndex: AnyRecord = await readJson(slugIndexPath, {})
      // 同步寫入多種變體，避免因 URL 編碼/解碼差異造成查不到
      const variants = new Set<string>()
      const raw = String(postSlug)
      variants.add(raw)
      try { variants.add(decodeURIComponent(raw)) } catch {}
      try { variants.add(encodeURIComponent(raw)) } catch {}
      // 也嘗試將 encode 後再 decode 與 decode 後再 encode，以盡可能統一
      try { variants.add(decodeURIComponent(encodeURIComponent(raw))) } catch {}
      try { variants.add(encodeURIComponent(decodeURIComponent(raw))) } catch {}
      variants.forEach((v) => { slugIndex[v] = String(id) })
      await writeJson(slugIndexPath, slugIndex)
    }
  } catch {}

  // 控制上限，避免無限成長
  await limitItems(typeDir, indexPath)

  return { success: true }
})
