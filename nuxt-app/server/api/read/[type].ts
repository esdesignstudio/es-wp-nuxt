import { promises as fs } from 'fs'
import path from 'path'

export default defineEventHandler(async (event) => {
    const type = event.context.params?.type
    const query = getQuery(event)
    const id = query.id
    const slug = query.slug
    const pageParam = query.page ? Number(Array.isArray(query.page) ? query.page[0] : query.page) : undefined
    const perPageParam = query.per_page ? Number(Array.isArray(query.per_page) ? query.per_page[0] : query.per_page) : undefined
    const cateSlug = typeof query.cate === 'string' ? query.cate : (Array.isArray(query.cate) ? String(query.cate[0]) : undefined)
    // 僅在啟用 i18n 時才使用 locale 參數
    const publicConfig = useRuntimeConfig().public as any
    const i18nEnabled = !!publicConfig?.i18nEnabled
    const locale = i18nEnabled ? (typeof query.locale === 'string' ? query.locale : (Array.isArray(query.locale) ? String(query.locale[0]) : undefined)) : undefined
    const keyword = typeof (query as any).q === 'string' && (query as any).q.trim() ? String((query as any).q).toLowerCase() : undefined

    if (!type) {
        throw createError({
            statusCode: 400,
            message: '缺少必要的type參數'
        })
    }

    try {
        const cacheRoot = path.join(process.cwd(), 'server', 'cache')
        if (type === 'global') {
            const globalPath = path.join(cacheRoot, 'global.json')
            try {
                const text = await fs.readFile(globalPath, 'utf-8')
                return JSON.parse(text)
            } catch {
                // 嘗試從來源建立 global 快取
                try {
                    const data = await $fetch('/api/save/global')
                    await fs.writeFile(globalPath, JSON.stringify(data), 'utf-8').catch(() => {})
                    return data
                } catch {
                    // 最後保底回傳空物件，避免 404 導致畫面壞掉
                    return {}
                }
            }
        }

        const typeDir = path.join(cacheRoot, type)
        const indexPath = path.join(typeDir, 'index.json')
        const slugIndexPath = path.join(typeDir, 'slug-index.json')

        // 關鍵字搜尋（目前支援 product/news 等以 post/title 為主的集合）
        if (keyword) {
            const idsText = await fs.readFile(indexPath, 'utf-8').catch(() => '[]')
            const ids: string[] = JSON.parse(idsText)
            if ((!ids || ids.length === 0)) {
                // 後相容：若無索引，讀舊單檔並在記憶體中處理
                const legacyPath = path.join(cacheRoot, `${type}.json`)
                const legacyText = await fs.readFile(legacyPath, 'utf-8').catch(() => null)
                if (legacyText) {
                    const legacyJson = JSON.parse(legacyText) as Record<string, any>
                    const items: Record<string, any> = {}
                    for (const [rid, rec] of Object.entries(legacyJson)) {
                        const langData = (i18nEnabled && locale) ? (rec as any)?.[locale] : (rec as any)
                        const title = String(langData?.post?.post_title || '').toLowerCase()
                        const tags = Array.isArray(langData?.product_tag) ? langData.product_tag : []
                        const typeStr = String(langData?.spec?.type || '').toLowerCase()
                        const match = title.includes(keyword)
                            || (Array.isArray(tags) && tags.some((t: any) => String(t?.name || '').toLowerCase().includes(keyword)))
                            || typeStr.includes(keyword)
                        if (match) {
                            items[String(rid)] = langData || rec
                        }
                    }
                    return { items, total: Object.keys(items).length }
                }
            }

            const concurrency = Number(process.env.CACHE_READ_CONCURRENCY || 8)
            const items: Record<string, any> = {}
            let i = 0
            while (i < ids.length) {
                const chunk = ids.slice(i, i + concurrency)
                const records = await Promise.all(
                    chunk.map(async (rid) => {
                        const itemPath = path.join(typeDir, `${rid}.json`)
                        const text = await fs.readFile(itemPath, 'utf-8').catch(() => null)
                        if (!text) return null
                        try {
                            const obj = JSON.parse(text)
                            const langData = (i18nEnabled && locale) ? obj?.[locale] : obj
                            const title = String(langData?.post?.post_title || '').toLowerCase()
                            const tags = Array.isArray(langData?.product_tag) ? langData.product_tag : []
                            const typeStr = String(langData?.spec?.type || '').toLowerCase()
                            const match = title.includes(keyword)
                                || (Array.isArray(tags) && tags.some((t: any) => String(t?.name || '').toLowerCase().includes(keyword)))
                                || typeStr.includes(keyword)
                            if (match) return { id: String(rid), data: langData || obj }
                            return null
                        } catch { return null }
                    })
                )
                records.forEach((r) => { if (r) items[r.id] = r.data })
                i += concurrency
            }
            return { items, total: Object.keys(items).length }
        }

        // 0) 分頁/分類查詢（不影響既有 API 行為）
        if ((pageParam && perPageParam) || cateSlug) {
            const idsText = await fs.readFile(indexPath, 'utf-8').catch(() => '[]')
            const ids: string[] = JSON.parse(idsText)
            if ((!ids || ids.length === 0)) {
                // 後相容：若無索引，讀舊單檔並在記憶體中處理
                const legacyPath = path.join(cacheRoot, `${type}.json`)
                const legacyText = await fs.readFile(legacyPath, 'utf-8').catch(() => null)
                if (legacyText) {
                    const legacyJson = JSON.parse(legacyText) as Record<string, any>
                    // 轉為 {id, date, include}
                    const entries = Object.entries(legacyJson).map(([rid, rec]) => {
                        const date = (i18nEnabled && locale ? (rec as any)?.[locale]?.post?.post_date : (rec as any)?.post?.post_date) || '1970-01-01T00:00:00'
                        const include = cateSlug ? (() => {
                            // 同時支援 news 的 categories 與 product 的 product_category
                            const langRec = (i18nEnabled && locale) ? (rec as any)?.[locale] : rec
                            const catsA = Array.isArray(langRec?.categories) ? langRec.categories : []
                            const catsB = Array.isArray(langRec?.product_category) ? langRec.product_category : []
                            const all = ([] as any[]).concat(catsA, catsB)
                            try { return all.some((c: any) => String(c?.slug) === cateSlug) } catch { return false }
                        })() : true
                        return { id: String(rid), date: new Date(date).getTime(), include }
                    })
                    const filtered = entries.filter(e => e.include)
                    filtered.sort((a, b) => b.date - a.date)
                    const total = filtered.length
                    const page = pageParam || 1
                    const perPage = perPageParam || total || 1
                    const start = (page - 1) * perPage
                    const end = start + perPage
                    const pageIds = filtered.slice(start, end).map(e => e.id)
                    const items: Record<string, any> = {}
                    for (const rid of pageIds) {
                        items[rid] = legacyJson[rid]
                    }
                    return { items, total }
                }
            }

            // 一般情境：讀取每筆檔案萃取必要資訊以排序與篩選
            const concurrency = Number(process.env.CACHE_READ_CONCURRENCY || 8)
            type Meta = { id: string, date: number, include: boolean }
            const metas: Meta[] = []
            let i = 0
            while (i < ids.length) {
                const chunk = ids.slice(i, i + concurrency)
                const records = await Promise.all(
                    chunk.map(async (rid) => {
                        const itemPath = path.join(typeDir, `${rid}.json`)
                        const text = await fs.readFile(itemPath, 'utf-8').catch(() => null)
                        if (!text) return null
                        try {
                            const obj = JSON.parse(text)
                            const d = (i18nEnabled && locale ? obj?.[locale]?.post?.post_date : obj?.post?.post_date) || '1970-01-01T00:00:00'
                            const include = cateSlug ? (() => {
                                const langRec = (i18nEnabled && locale) ? obj?.[locale] : obj
                                const catsA = Array.isArray(langRec?.categories) ? langRec.categories : []
                                const catsB = Array.isArray(langRec?.product_category) ? langRec.product_category : []
                                const all = ([] as any[]).concat(catsA, catsB)
                                try { return all.some((c: any) => String(c?.slug) === cateSlug) } catch { return false }
                            })() : true
                            return { id: String(rid), date: new Date(d).getTime(), include } as Meta
                        } catch { return null }
                    })
                )
                records.forEach((m) => { if (m) metas.push(m) })
                i += concurrency
            }

            const filtered = metas.filter(m => m.include)
            filtered.sort((a, b) => b.date - a.date)
            const total = filtered.length
            const page = pageParam || 1
            const perPage = perPageParam || total || 1
            const start = (page - 1) * perPage
            const end = start + perPage
            const pageIds = filtered.slice(start, end).map(e => e.id)

            // 回讀該頁的完整資料
            const items: Record<string, any> = {}
            for (const rid of pageIds) {
                const itemPath = path.join(typeDir, `${rid}.json`)
                const text = await fs.readFile(itemPath, 'utf-8').catch(() => null)
                if (text) items[rid] = JSON.parse(text)
            }
            return { items, total }
        }

        // 1) id 精準讀取（單檔）
        if (id) {
            const idStr = Array.isArray(id) ? String(id[0]) : String(id)
            const itemPath = path.join(typeDir, `${idStr}.json`)
            const text = await fs.readFile(itemPath, 'utf-8').catch(() => null)
            if (text) {
                const obj = JSON.parse(text)
                try { (obj as any).__id = idStr } catch {}
                return obj
            }
            // 後相容：單檔快取
            const legacyPath = path.join(cacheRoot, `${type}.json`)
            const legacyText = await fs.readFile(legacyPath, 'utf-8').catch(() => null)
            if (legacyText) {
                const legacyJson = JSON.parse(legacyText)
                if (legacyJson && Object.prototype.hasOwnProperty.call(legacyJson, idStr)) {
                    const obj = legacyJson[idStr]
                    try { (obj as any).__id = idStr } catch {}
                    return obj
                }
            }
        }

        // 2) slug 轉 id 再讀單檔（所有類型都支援，包含 page）
        if (slug) {
            const slugIndexText = await fs.readFile(slugIndexPath, 'utf-8').catch(() => '{}')
            const slugIndex = JSON.parse(slugIndexText) as Record<string, string>
            const raw = Array.isArray(slug) ? String(slug[0]) : String(slug)

            // 嘗試多種編碼與 Unicode 正規化，避免中英文混雜與 URL 編碼差異造成查不到
            const normalizeAll = (s: string) => [
                s,
                (() => { try { return decodeURIComponent(s) } catch { return undefined } })(),
                (() => { try { return encodeURIComponent(s) } catch { return undefined } })(),
                (() => { try { return s.normalize('NFC') } catch { return undefined } })(),
                (() => { try { return s.normalize('NFD') } catch { return undefined } })(),
                (() => { try { return s.normalize('NFKC') } catch { return undefined } })(),
                (() => { try { return s.normalize('NFKD') } catch { return undefined } })()
            ].filter(Boolean) as string[]

            const candidates = new Set<string>([
                ...normalizeAll(raw)
            ])
            // 交叉再做一次 encode/decode 的正規化
            Array.from(candidates).forEach((c) => {
                normalizeAll(c).forEach((x) => candidates.add(x))
            })

            let mappedId: string | undefined
            for (const key of candidates) {
                if (slugIndex[key]) { mappedId = slugIndex[key]; break }
            }

            if (mappedId) {
                const itemPath = path.join(typeDir, `${mappedId}.json`)
                try {
                    const text = await fs.readFile(itemPath, 'utf-8')
                    const obj = JSON.parse(text)
                    try { (obj as any).__id = mappedId } catch {}
                    return obj
                } catch {}
            }
            // 後相容：掃描單檔快取
            const legacyPath = path.join(cacheRoot, `${type}.json`)
            const legacyText = await fs.readFile(legacyPath, 'utf-8').catch(() => null)
            if (legacyText) {
                const legacyJson = JSON.parse(legacyText) as Record<string, any>
                const match = Object.values(legacyJson).filter((v: any) => {
                    const pn = v?.post?.post_name
                    if (!pn) return false
                    return candidates.has(String(pn))
                        || ((() => { try { return candidates.has(decodeURIComponent(String(pn))) } catch { return false } })())
                        || ((() => { try { return candidates.has(encodeURIComponent(String(pn))) } catch { return false } })())
                })
                if (match.length > 0) {
                    match.sort((a: any, b: any) => new Date(b.post.post_date).getTime() - new Date(a.post.post_date).getTime())
                    return match[0]
                }
            }
        }

        // 3) 無參數時：讀取索引並批次讀檔（限制併發，避免一次吃爆記憶體）
        const idsText = await fs.readFile(indexPath, 'utf-8').catch(() => '[]')
        const ids: string[] = JSON.parse(idsText)
        if (!ids || ids.length === 0) {
            // 後相容：若無索引，讀取舊單檔並直接返回
            const legacyPath = path.join(cacheRoot, `${type}.json`)
            const legacyText = await fs.readFile(legacyPath, 'utf-8').catch(() => null)
            if (legacyText) return JSON.parse(legacyText)
        }
        const concurrency = Number(process.env.CACHE_READ_CONCURRENCY || 8)

        const results: Record<string, any> = {}
        let i = 0
        while (i < ids.length) {
            const chunk = ids.slice(i, i + concurrency)
            const records = await Promise.all(
                chunk.map(async (rid) => {
                    const itemPath = path.join(typeDir, `${rid}.json`)
                    const text = await fs.readFile(itemPath, 'utf-8').catch(() => null)
                    return text ? { id: rid, data: JSON.parse(text) } : null
                })
            )
            records.forEach((r) => { if (r) results[r.id] = r.data })
            i += concurrency
        }
        return results
    } catch (error) {
        console.error(`讀取檔案時發生錯誤：${error}`)
        throw createError({
            statusCode: 404,
            message: '找不到對應的資料'
        })
    }
})
