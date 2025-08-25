export const usePageLoaded = () => useState<boolean>('isPageloaded', () => false)
export const useScrolled = () => useState<boolean>('isScrolled', () => false)
export const useSearchOpen = () => useState<boolean>('isSearchOpen', () => false)
export const useHeaderExpand = () => useState<boolean>('isHeaderExpand', () => false)
export const useHeaderWhite = () => useState<boolean>('isHeaderWhite', () => false)
export const useMenuOpen = () => useState<boolean>('isMenuOpen', () => false)

// 舊版快取的 sizes 物件（維持相容用）
interface ImageSizes {
    medium?: string
    medium_large?: string
    large?: string
    thumbnail?: string
    ['1536x1536']?: string
    ['2048x2048']?: string
    [key: string]: string | undefined
}

// 新版：只保留單一檔案的資訊
interface BaseImageInfo {
    url: string
    width?: number
    height?: number
}

interface SrcSetConfig {
    size: string
    width: number
}

// 產生相容於舊資料與新資料的 srcset
export const useResponsiveImage = (
    input?: ImageSizes | BaseImageInfo | string
) => {
    if (!input) return ''

    // 將舊資料中的 localhost 網域正規化為目前環境設定的 API 網域
    const normalizeOrigin = (rawUrl: string): string => {
        try {
            if (!rawUrl) return rawUrl
            const config = useRuntimeConfig().public
            const targetOrigin = new URL(config.apiUrl).origin
            const parsed = new URL(rawUrl)
            // 僅在來源是 localhost 或容器預設主機名時替換
            const isLocalHost = parsed.hostname === 'localhost' || parsed.hostname === 'nuxt-app' || parsed.hostname === 'host.docker.internal'
            if (!isLocalHost) return rawUrl
            return `${targetOrigin}${parsed.pathname}${parsed.search}${parsed.hash}`
        } catch {
            return rawUrl
        }
    }

    const isBaseImageInfo = (value: unknown): value is BaseImageInfo => {
        return typeof value === 'object' && value !== null && 'url' in (value as Record<string, unknown>)
    }

    // 若為新格式（或只給 url 字串），依 WordPress 命名規則自動組出尺寸檔名
    const buildFromBase = (base: BaseImageInfo | string): string => {
        const baseInfo: BaseImageInfo = typeof base === 'string' ? { url: base } : base
        const { url, width: originalWidth, height: originalHeight } = baseInfo

        if (!url) return ''
        const normalizedUrl = normalizeOrigin(url)

        const aspectRatio = originalWidth && originalHeight ? originalHeight / originalWidth : undefined
        const sizePlan: Array<{ width: number; height?: number }> = [
            { width: 150, height: 150 }, // 縮圖（WP 預設為方形）
            { width: 300 },
            { width: 563 }, // medium_large 近似寬度（依比例算高）
            { width: 992 },
            { width: 1219 },
            { width: 1400 },
            { width: 1536 },
            { width: 2048 },
        ]

        const match = normalizedUrl.match(/(.+)(\.[a-zA-Z0-9]+)$/)
        const [name, ext] = match ? [match[1], match[2]] as const : [normalizedUrl, '']

        const toSizedUrl = (w: number, h?: number) => {
            const finalH = h ?? (aspectRatio ? Math.round(w * aspectRatio) : undefined)
            if (!finalH || !ext) return `${name}${ext}`
            return `${name}-${w}x${finalH}${ext}`
        }

        const entries: string[] = []
        for (const plan of sizePlan) {
            const h = plan.height ?? (aspectRatio ? Math.round(plan.width * aspectRatio) : undefined)
            if (!h) continue
            entries.push(`${toSizedUrl(plan.width, h)} ${plan.width}w`)
        }

        // 原圖（或未知寬度時僅放 URL 作為最後 fallback）
        if (originalWidth) {
            entries.push(`${normalizedUrl} ${originalWidth}w`)
        } else {
            entries.push(normalizedUrl)
        }

        return entries.join(',\n')
    }

    // 舊格式：sizes 物件仍可直接使用
    const buildFromSizes = (sizes: ImageSizes): string => {
        const srcSetConfig: SrcSetConfig[] = [
            { size: 'thumbnail', width: 150 },
            { size: 'medium', width: 300 },
            { size: 'medium_large', width: 992 },
            { size: 'large', width: 1400 },
            { size: '1536x1536', width: 1536 },
            { size: '2048x2048', width: 2048 },
        ]

        return srcSetConfig
            .map(({ size, width }) => sizes[size] ? `${normalizeOrigin(sizes[size] as string)} ${width}w` : '')
            .filter(Boolean)
            .join(',\n')
    }

    if (typeof input === 'string') return buildFromBase(input)
    if (isBaseImageInfo(input)) return buildFromBase(input)
    return buildFromSizes(input as ImageSizes)
}

// 僅提供可序列化的初始值，避免 SSR 時 devalue 非 POJO 錯誤
export const useGlobal = () => useState<Record<string, any>>('globalOption', () => ({}))

// 如需遠端載入，可用此 helper 並確保回寫純物件
export const loadGlobal = async () => {
    const config = useRuntimeConfig().public
    const maybeLocale = (() => {
        try {
            // 若專案啟用 i18n，且前端可取得語系代碼，則攜帶 locale
            if ((config as any).i18nEnabled && typeof (globalThis as any).$i18n?.locale?.value === 'string') {
                return (globalThis as any).$i18n.locale.value
            }
        } catch {}
        return undefined
    })()
    try {
        const data = await $fetch(config.apiUrl + '/get_global', {
            method: 'GET',
            params: maybeLocale ? { locale: maybeLocale } : undefined
        })
        // 確保為 POJO
        const plain = JSON.parse(JSON.stringify(data))
        useGlobal().value = plain
    } catch (e) {
        // 失敗時維持現狀，避免寫入非 POJO
        console.error('loadGlobal failed:', e)
    }
}

const __pageDataPending: Record<string, Promise<any>> = {}
const __collectionPending: Record<string, Promise<any>> = {}

const usePageData = (collection = '', slugOrId = '') => {
    // 單例狀態：僅預載 global，其餘依需求讀取
    const pageData = useState<Record<string, any>>('pageData', () => ({} as Record<string, any>))

    // 若無參數，回傳整體狀態（維持相容）
    if (!collection || !slugOrId) {
        return pageData
    }

    // 參數齊全時，精準讀取單筆快取（id 或 slug）
    const ensureSingleRecord = async () => {
        const targetCollection = collection.trim()
        const rawKey = String(slugOrId).trim()
        // 提供 'options' 簡寫，會映射為 '{collection}_options'
        const targetKey = rawKey === 'options' ? `${targetCollection}_options` : rawKey
        // 視為 id 的情境：純數字或 *_options（例如 product_options/news_options/page_options）
        const isId = /^\d+$/.test(targetKey) || /_options$/.test(targetKey)

        // 確保 collection 容器存在
        if (!pageData.value[targetCollection]) {
            pageData.value[targetCollection] = {}
        }

        // 1) 若是 id 且已載入，直接回傳
        if (isId) {
            const existed = pageData.value[targetCollection][targetKey]
            if (existed) return existed
        }

        // 2) 發出請求（id 直讀；否則以 slug 查表取得 id）
        const params: Record<string, string> = isId ? { id: targetKey } : { slug: decodeURIComponent(targetKey) }
        // 若啟用 i18n，夾帶當前語系參數
        try {
            const cfg = useRuntimeConfig().public as any
            const maybeLocale = (cfg?.i18nEnabled && typeof (globalThis as any).$i18n?.locale?.value === 'string') ? (globalThis as any).$i18n.locale.value : undefined
            if (maybeLocale) (params as any).locale = String(maybeLocale)
        } catch {}
        try {
            // 以 $fetch 取代 useFetch，避免已掛載後的 useFetch 警告
            let record: any = await $fetch<any>(`/api/read/${targetCollection}` as any, { params })
            if (!record) return undefined

            // 推斷 id：優先使用現有 id 參數；其次從單語系物件的 __id 或 post.ID；最後掃描多語系物件
            let resolvedId = isId ? targetKey : ''
            if (!resolvedId) {
                const injectedId = (record as any)?.__id
                if (injectedId) resolvedId = String(injectedId)
            }
            if (!resolvedId) {
                const topLevelId = record?.post?.ID
                if (topLevelId) resolvedId = String(topLevelId)
            }
            if (!resolvedId) {
                try {
                    const locales = Object.keys(record)
                    for (const lang of locales) {
                        const maybeId = record?.[lang]?.post?.ID
                        if (maybeId) { resolvedId = String(maybeId); break }
                    }
                } catch {}
            }

            // 額外保底：仍無法判定 id（可能後端回傳了整包或結構異常）→ 讀整包後掃 slug 對應
            if (!resolvedId && !isId && targetKey) {
                try {
                    const candidates = new Set<string>()
                    candidates.add(targetKey)
                    try { candidates.add(decodeURIComponent(targetKey)) } catch {}
                    try { candidates.add(encodeURIComponent(targetKey)) } catch {}

                    const all: Record<string, any> = await $fetch<any>(`/api/read/${targetCollection}` as any)
                    for (const [rid, rec] of Object.entries(all || {})) {
                        // 單語系
                        const pnTop = (rec as any)?.post?.post_name
                        if (pnTop && candidates.has(String(pnTop))) { resolvedId = String(rid); break }
                        // 多語系
                        const langs = Object.keys(rec || {})
                        for (const lg of langs) {
                            const pn = rec?.[lg]?.post?.post_name
                            if (!pn) continue
                            const hit = candidates.has(String(pn))
                                || ((() => { try { return candidates.has(decodeURIComponent(String(pn))) } catch { return false } })())
                                || ((() => { try { return candidates.has(encodeURIComponent(String(pn))) } catch { return false } })())
                            if (hit) { resolvedId = String(rid); break }
                        }
                        if (resolvedId) break
                    }

                    if (resolvedId) {
                        // 若從整包找到，改用該筆資料
                        // 並覆寫 record 以寫入正確 id 鍵
                        const chosen: any = (all as any)[resolvedId]
                        if (chosen) record = chosen
                    }
                } catch {}
            }
            const stashId = resolvedId || targetKey

            // 寫回快取狀態（POJO）
            const plain = JSON.parse(JSON.stringify(record))
            pageData.value[targetCollection][stashId] = plain

            // 回傳單語系物件
            return plain
        } catch (error) {
            console.error('usePageData precise read failed:', error)
            return undefined
        }
    }

    // 回傳一個臨時 Ref，承載該筆資料的指定語系內容
    const single = useState<any>(`pageData:${collection}:${slugOrId}`, () => undefined)
    if (single.value === undefined) {
        // 去重機制：避免同鍵重複請求（不放進 state，避免 SSR 序列化問題）
        const pendingKey = `${collection}:${slugOrId === 'options' ? `${collection}_options` : slugOrId}`
        const run = () => ensureSingleRecord().then((val) => { single.value = val; return val })
        if (!__pageDataPending[pendingKey]) {
            __pageDataPending[pendingKey] = run().finally(() => { delete __pageDataPending[pendingKey] })
        }
    }
    return single
}

// 需要「保證已載入」時使用（支援頂層 await）
export const awaitPageData = async (collection: string, slugOrId: string) => {
    const refData = usePageData(collection, slugOrId)
    const pendingKey = `${collection}:${slugOrId === 'options' ? `${collection}_options` : slugOrId}`
    const p = __pageDataPending[pendingKey]
    if (p) await p
    return refData.value
}

// 讀取整包 collection 快取（例如 'product'、'news'、'page'）
export const awaitCollectionData = async (collection: string, options?: { force?: boolean }) => {
    const pageData = useState<Record<string, any>>('pageData', () => ({} as Record<string, any>))
    const force = options?.force === true
    if (!force && pageData.value[collection]) return pageData.value[collection]

    const pendingKey = `collection:${collection}`
    if (!__collectionPending[pendingKey]) {
        __collectionPending[pendingKey] = $fetch(`/api/read/${collection}`)
            .then((data) => {
                const plain = JSON.parse(JSON.stringify(data))
                pageData.value[collection] = plain
                return plain
            })
            .finally(() => { delete __collectionPending[pendingKey] })
    }
    return await __collectionPending[pendingKey]
}

// 部分載入：依頁碼與每頁筆數讀取，並將返回項目合併到 pageData 快取
export const awaitCollectionPage = async (
    collection: string,
    params: { page: number; perPage: number; cate?: string }
) => {
    const pageData = useState<Record<string, any>>('pageData', () => ({} as Record<string, any>))
    const { page, perPage, cate } = params
    const query: Record<string, any> = { page, per_page: perPage }
    if (cate) query.cate = cate
    try {
        const cfg = useRuntimeConfig().public as any
        const maybeLocale = (cfg?.i18nEnabled && typeof (globalThis as any).$i18n?.locale?.value === 'string') ? (globalThis as any).$i18n.locale.value : undefined
        if (maybeLocale) query.locale = String(maybeLocale)
    } catch {}

    const resp = await $fetch<{ items: Record<string, any>; total: number }>(`/api/read/${collection}` as any, { params: query })
    if (!pageData.value[collection]) pageData.value[collection] = {}
    // 合併單筆到快取
    const plainItems = JSON.parse(JSON.stringify(resp.items)) as Record<string, any>
    Object.entries(plainItems).forEach(([rid, rec]) => {
        pageData.value[collection][rid] = rec
    })
    return resp
}

// 關鍵字搜尋：依 q 與語系載入集合中符合的項目，並合併到 pageData 快取
export const awaitCollectionSearch = async (
    collection: string,
    params: { q: string }
) => {
    const pageData = useState<Record<string, any>>('pageData', () => ({} as Record<string, any>))
    const { q } = params
    const query: Record<string, any> = { q }
    try {
        const cfg = useRuntimeConfig().public as any
        const maybeLocale = (cfg?.i18nEnabled && typeof (globalThis as any).$i18n?.locale?.value === 'string') ? (globalThis as any).$i18n.locale.value : undefined
        if (maybeLocale) query.locale = String(maybeLocale)
    } catch {}

    const resp = await $fetch<{ items: Record<string, any>; total: number }>(`/api/read/${collection}` as any, { params: query })
    if (!pageData.value[collection]) pageData.value[collection] = {}
    const plainItems = JSON.parse(JSON.stringify(resp.items)) as Record<string, any>
    Object.entries(plainItems).forEach(([rid, rec]) => {
        pageData.value[collection][rid] = rec
    })
    return resp
}

// 統一入口：取得單筆、整包或分頁資料
export const getPageData = async (options: {
    collection: string
    key?: string | number
    whole?: boolean
    page?: { page: number; perPage: number; cate?: string }
    search?: { q: string }
    force?: boolean
}) => {
    const { collection, key, whole, page, search, force } = options
    if (page) {
        return await awaitCollectionPage(collection, page)
    }
    if (search) {
        return await awaitCollectionSearch(collection, search)
    }
    if (whole) {
        return await awaitCollectionData(collection, { force })
    }
    if (key !== undefined) {
        return await awaitPageData(collection, String(key))
    }
    // 回傳已存在於快取的集合，或強制讀整包
    const state = useState<Record<string, any>>('pageData', () => ({} as Record<string, any>))
    if (!force && state.value[collection]) return state.value[collection]
    return await awaitCollectionData(collection, { force })
}

export const useViewport = () => useState<object>('viewport', () => ({
    width: process.client ? window.innerWidth : 0,
    height: process.client ? window.innerHeight : 0,
    isMobile: process.client ? window.innerWidth < 768 : false,
    isDesktop: process.client ? window.innerWidth >= 1025 : false,
}))

export const useUpdateViewport = () => {
    useViewport().value = {
        width: process.client ? window.innerWidth : 0,
        height: process.client ? window.innerHeight : 0,
        isMobile: process.client ? window.innerWidth < 768 : false,
        isDesktop: process.client ? window.innerWidth >= 1025 : false,
    }

    if (process.client) {
        window.addEventListener('resize', useUpdateViewport)
    }
}

export const filterLink = (link: string) => {
    if (link.includes('http')) {
        const url = new URL(link)
        
        // 檢查是否為當前網域
        if (process.client && url.host === window.location.host) {
            // 保留路徑和錨點
            return url.pathname + url.hash
        }
        return link
    }
    return link
}