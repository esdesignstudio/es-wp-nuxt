import { useLogs } from '~/composables/useLogs'

export default defineEventHandler(async (event) => {
    const { logInfo, logError } = useLogs()
    
    try {
        // 取得 Nitro 的 cache storage
        const storage = useStorage('cache')

        // 收集所有要清除的快取類型
        const cacheTypes = [
            { name: 'Route Cache', prefix: 'nitro:routes' },
            { name: 'API Cache', prefix: 'nitro:handlers:fetch-cache' }
        ]

        const results = {
            success: true,
            message: '',
            details: [] as any[],
            totalCount: 0,
            deletedKeys: [] as string[]
        }

        // 清除每種類型的快取
        for (const cacheType of cacheTypes) {
            const keys = await storage.getKeys(cacheType.prefix)
            
            if (keys.length > 0) {
                logInfo(`[Cache Clear All]`)
                
                for (const key of keys) {
                    await storage.removeItem(key)
                    results.deletedKeys.push(key)
                }
                
                results.details.push({
                    type: cacheType.name,
                    count: keys.length
                })
                
                results.totalCount += keys.length
            }
        }

        if (results.totalCount === 0) {
            return {
                success: true,
                message: '目前沒有任何快取',
                totalCount: 0,
            }
        }

        results.message = `成功清除所有快取 (共 ${results.totalCount} 筆)`

        return results
    } catch (error: any) {
        logError('clear-all-cache', {}, 0, error.statusCode || 500, 'POST', error.message)
        return {
            success: false,
            message: '清除所有快取時發生錯誤',
            error: error.message,
        }
    }
})

