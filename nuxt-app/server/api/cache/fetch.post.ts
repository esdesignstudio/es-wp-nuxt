/**
 * 通用的 API 快取代理
 * 
 * 參數:
 * - apiUrl: 要呼叫的 API URL
 * - method: GET 或 POST
 * - cache: 是否啟用快取
 * 
 * 使用範例:
 * POST /api/cache/fetch
 * {
 *   "apiUrl": "http://localhost:9000/wp-json/api/get_page_custom?id=26",
 *   "method": "GET",
 *   "cache": true
 * }
 */

import { useLogs } from '~/composables/useLogs'

/**
 * 生成快取 key
 * 將所有特殊字符替換為 - 避免產生目錄結構
 */
function generateCacheKey(apiUrl: string, method: string): string {
    try {
        const url = new URL(apiUrl)
        
        // 保留 URL 編碼格式，不解碼（這樣可以完整保留原始資訊）
        let path = url.pathname.replace(/^\//, '')  // 移除開頭的 /
        let queryString = url.search.replace('?', '')
        
        // 組合完整的 key，並將所有特殊字符替換為 -
        const fullKey = queryString 
            ? `${method.toLowerCase()}-${path}-${queryString}`
            : `${method.toLowerCase()}-${path}`
        
        // 替換所有非字母數字字符為 -，避免產生目錄結構
        // 但保留 % 符號（URL 編碼需要）
        return fullKey.replace(/[^a-zA-Z0-9%]/g, '-')
    } catch (error) {
        // 如果不是完整 URL，直接使用
        return `${method.toLowerCase()}-${apiUrl.replace(/[^a-zA-Z0-9%]/g, '-')}`
    }
}

/**
 * 主要的 handler
 */
export default defineEventHandler(async (event) => {
    const startTime = Date.now()
    const { logSuccess, logError } = useLogs()
    
    // 讀取 request body
    const body = await readBody(event)
    const { apiUrl, method = 'GET', cache = false } = body
    
    // 驗證參數
    if (!apiUrl) {
        throw createError({
            statusCode: 400,
            message: '缺少必要參數: apiUrl'
        })
    }
    
    if (!['GET', 'POST'].includes(method)) {
        throw createError({
            statusCode: 400,
            message: 'method 只能是 GET 或 POST'
        })
    }
    
    // 生成快取 key
    const cacheKey = generateCacheKey(apiUrl, method)
    
    // 如果啟用快取，檢查是否有快取
    if (cache) {
        const storage = useStorage('cache')
        const cacheData = await storage.getItem(`nitro:handlers:fetch-cache:${cacheKey}`)
        
        if (cacheData) {
            const duration = Date.now() - startTime
            
            // 從快取取得 - 使用 useLogs 記錄
            logSuccess(apiUrl, {}, duration, 200, method, true)
            
            return cacheData
        }
    }
    
    // 沒有快取，發送請求
    try {
        const data = await $fetch(apiUrl, {
            method: method as 'GET' | 'POST'
        })
        
        // 如果啟用快取，儲存快取
        if (cache) {
            const storage = useStorage('cache')
            await storage.setItem(`nitro:handlers:fetch-cache:${cacheKey}`, data as any)
        }
        
        const duration = Date.now() - startTime
        
        // 新資料 - 使用 useLogs 記錄
        logSuccess(apiUrl, {}, duration, 200, method, false)
        
        return data
    } catch (error: any) {
        const duration = Date.now() - startTime
        
        // 錯誤 - 使用 useLogs 記錄
        logError(apiUrl, {}, duration, error.statusCode || 500, method, error.message)
        
        throw createError({
            statusCode: error.statusCode || 500,
            message: error.message || '請求失敗'
        })
    }
})

