export const usePageLoaded = () => useState<boolean>('isPageloaded', () => false)
export const useScrolled = () => useState<boolean>('isScrolled', () => false)
export const useSearchOpen = () => useState<boolean>('isSearchOpen', () => false)
export const useHeaderExpand = () => useState<boolean>('isHeaderExpand', () => false)
export const useHeaderWhite = () => useState<boolean>('isHeaderWhite', () => false)
export const useMenuOpen = () => useState<boolean>('isMenuOpen', () => false)


// 僅提供可序列化的初始值，避免 SSR 時 devalue 非 POJO 錯誤
export const useGlobal = () => useState<Record<string, any>>('globalOption', () => ({}))


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