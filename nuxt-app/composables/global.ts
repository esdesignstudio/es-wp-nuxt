export const usePageLoaded = () => useState<boolean>('isPageloaded', () => false)
export const useScrolled = () => useState<boolean>('isScrolled', () => false)
export const useSearchOpen = () => useState<boolean>('isSearchOpen', () => false)
export const useHeaderExpand = () => useState<boolean>('isHeaderExpand', () => false)
export const useHeaderWhite = () => useState<boolean>('isHeaderWhite', () => false)
export const useMenuOpen = () => useState<boolean>('isMenuOpen', () => false)

interface ImageSizes {
    medium?: string
    medium_large?: string
    large?: string
    [key: string]: string | undefined
}

interface SrcSetConfig {
    size: string
    width: number
}

export const useResponsiveImage = (sizes: ImageSizes) => {
    if (!sizes) return ''
    const srcSetConfig: SrcSetConfig[] = [
        { size: 'thumbnail', width: 150 },
        { size: 'medium', width: 300 },
        { size: 'medium_large', width: 768 },
        { size: 'large', width: 1024 },
        { size: '1536x1536', width: 1536 },
        { size: '2048x2048', width: 2048 },
    ]

    const srcset = srcSetConfig
        .map(({ size, width }) => sizes[size] ? `${sizes[size]} ${width}w` : '')
        .filter(Boolean)
        .join(',\n')

    return srcset
}

export const useGlobal = () => useState<Object>('globalOption', () => {
    
    const config = useRuntimeConfig().public
    
    const { data } = useAsyncData(
        'get_globa_api',
        () => $fetch( config.apiUrl + '/get_global', {
            method: 'GET'
        })
    )

    return data
})

export const usePageData = (collection = '', slug = '') => {
    // 使用 useState 儲存頁面的資料
    const pageData = useState<Object>('pageData', () => ({}))

    // 如果有指定 collection 和 slug，則從 pageData 中根據 collection 和 slug 查找對應的資料
    if (collection && slug) {
        const collectionData = pageData.value[collection]

        // 查找符合 slug 的資料
        if (collectionData) {
            const matchingEntries = Object.entries(collectionData).filter(([_, value]) =>
                value.post && (value.post.post_title === slug || value.post.post_name === slug)
            )

            if (matchingEntries.length > 0) {
                // 如果有多個匹配項，按 post_date 排序並返回最新的
                const sortedEntries = matchingEntries.sort((a, b) => 
                    new Date(b[1].post.post_date).getTime() - new Date(a[1].post.post_date).getTime()
                )
                return sortedEntries[0][1] // 返回符合條件的文章
            }
        }
    }

    return pageData
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