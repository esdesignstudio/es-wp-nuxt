import { useLogs } from '~/composables/useLogs'

export default defineEventHandler(async (event) => {
    const { logInfo, logError } = useLogs()
    
    try {
        // 從 request body 取得要清除的路徑
        const body = await readBody(event)
        const { path } = body

        if (!path) {
            return {
                success: false,
                message: '請提供要清除的路徑 (path)',
            }
        }

        // 取得 Nitro 的 cache storage
        const storage = useStorage('cache')

        const results = {
            success: true,
            message: '',
            details: [] as any[],
            totalCount: 0,
            deletedKeys: [] as string[]
        }

        // 1. 清除 Route Cache (SWR)
        // Key 格式：nitro:routes:_:index.{hash}、nitro:routes:_:works:{slug}.{hash}
        // 直接用路徑前綴比對 key，不需要讀取 body 內容
        const allRouteKeys = await storage.getKeys('nitro:routes')
        const matchedRouteKeys: string[] = []
        
        // 將路徑轉換為 key 前綴格式：
        //   /        → index
        //   /works   → works
        //   /works/foo → works:foo
        //   /works/* → works:（前綴匹配）
        const isWildcard = path.endsWith('*')
        
        let targetKeyPrefix = path
            .replace(/^\//, '')          // 移除開頭 /
            .replace(/\*$/, '')          // 移除結尾 *
            .replace(/\//g, ':')         // / 轉換為 : (unstorage key separator)
            .toLowerCase()
        
        if (targetKeyPrefix === '' || targetKeyPrefix === ':') {
            targetKeyPrefix = 'index'
        }
        
        for (const key of allRouteKeys) {
            // key 格式: nitro:routes:_:index.{hash}
            const keyName = key.split(':').pop()?.toLowerCase() ?? ''
            
            let isMatch = false
            if (isWildcard) {
                // 通配符：匹配所有以 prefix 開頭的 key（e.g. works: 匹配 works:foo.hash）
                isMatch = keyName.startsWith(targetKeyPrefix)
            } else {
                // 精確路徑：key 名稱是 {targetKeyPrefix}.{hash}
                isMatch = keyName === targetKeyPrefix || keyName.startsWith(targetKeyPrefix + '.')
            }
            
            if (isMatch) {
                matchedRouteKeys.push(key)
                await storage.removeItem(key)
            }
        }

        if (matchedRouteKeys.length > 0) {
            matchedRouteKeys.forEach(key => {
                const displayKey = key.replace('nitro:routes:_:', '')
                logInfo(`[Cache Clear SWR] ${path} - ${displayKey}`)
            })
            
            results.details.push({
                type: 'Route Cache',
                count: matchedRouteKeys.length
            })
            results.totalCount += matchedRouteKeys.length
            results.deletedKeys.push(...matchedRouteKeys)
        }

        // 2. 清除 API Cache (fetch-cache)
        // 只有當 path 是 API 路徑時才清除 API Cache（包含 /wp-json/ 或 /api/）
        const allApiKeys = await storage.getKeys('nitro:handlers:fetch-cache')
        const matchedApiKeys: string[] = []

        // 判斷是否為 API 路徑
        const isApiPath = path.includes('/wp-json/') || path.includes('/api/')

        if (isApiPath) {
            // 將路徑轉換為 API Cache key 格式
            // 例如: /wp-json/api/get_collection_work?slug=netflix-series%ef%bd%9c... 
            //    -> get-wp-json-api-get-collection-work-slug-netflix-series%ef%bd%9c...
            // 注意: API Cache key 格式是 {method}-{path}，必須與 generateCacheKey 的邏輯一致
            // 保留 URL 編碼格式（不解碼）
            let normalizedApiPath = path  // 使用原始路徑（保留編碼）
                .replace(/^https?:\/\/[^/]+/, '') // 移除域名
                .replace(/^\//, '')               // 移除開頭的 /
                .replace(/[^a-zA-Z0-9%]/g, '-')   // 替換特殊字符為 -，但保留 %
                .toLowerCase()
            
            // 如果不是以 get- 開頭，則加上 get- 前綴（因為 API Cache key 格式是 {method}-{path}）
            if (!normalizedApiPath.startsWith('get-')) {
                normalizedApiPath = 'get-' + normalizedApiPath
            }
            
            
            for (const key of allApiKeys) {
                // key 格式: nitro:handlers:fetch-cache:get-wp-json-api-get-collection-work-list
                const keyPath = key.replace('nitro:handlers:fetch-cache:', '')
                
                // 統一轉換為小寫進行比對（URL 編碼的大小寫不影響實際意義）
                const keyPathLower = keyPath.toLowerCase()
                
                // 使用前綴匹配：清除所有以 normalizedApiPath 開頭的 API Cache
                // 例如: get-wp-json-api-get-collection-work-list 會匹配:
                //   - get-wp-json-api-get-collection-work-list
                //   - get-wp-json-api-get-collection-work-list-test-1
                const isMatch = keyPathLower === normalizedApiPath || keyPathLower.startsWith(normalizedApiPath + '-')
                
                if (isMatch) {
                    matchedApiKeys.push(key)
                    await storage.removeItem(key)
                }
            }
        }

        if (matchedApiKeys.length > 0) {
            matchedApiKeys.forEach(key => {
                const displayKey = key.replace('nitro:handlers:fetch-cache:', '')
                logInfo(`[Cache Clear API] ${path} - ${displayKey}`)
            })
            
            results.details.push({
                type: 'API Cache',
                count: matchedApiKeys.length
            })
            results.totalCount += matchedApiKeys.length
            results.deletedKeys.push(...matchedApiKeys)
        }

        // 回傳結果
        if (results.totalCount === 0) {
            return {
                success: false,
                message: `路徑 "${path}" 沒有找到任何快取`,
                hint: '請確認路徑是否正確。範例: /works (列表頁，含所有 query 變體), /works/gogoro-jego (單筆頁), /wp-json/api/get_collection_work_list (API 快取)',
            }
        }

        results.message = `成功清除路徑 "${path}" 的快取 (共 ${results.totalCount} 筆)`

        return results
    } catch (error: any) {
        logError('clear-cache', {}, 0, error.statusCode || 500, 'POST', error.message)
        return {
            success: false,
            message: '清除快取時發生錯誤',
            error: error.message,
        }
    }
})

