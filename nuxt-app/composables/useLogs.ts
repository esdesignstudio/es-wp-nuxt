import dayjs from 'dayjs'

/**
 * ANSI 顏色碼
 */
const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    cyan: '\x1b[36m',
}

/**
 * 格式化時間為 [YYYY-MM-DD:HH:mm:ss]
 */
function formatTimestamp(): string {
    return dayjs().format('[[]YYYY-MM-DD:HH:mm:ss[]]')
}

/**
 * 組合完整 URL（包含 query）
 */
function buildFullUrl(url: string, query: Record<string, any>): string {
    if (Object.keys(query).length === 0) {
        return url
    }
    
    const queryString = new URLSearchParams(query as any).toString()
    return `${url}?${queryString}`
}

/**
 * 根據執行時間決定顏色
 * < 100ms: 綠色
 * >= 100ms: 紅色
 */
function getDurationColor(duration: number): string {
    return duration < 100 ? colors.green : colors.red
}

/**
 * 根據狀態碼決定顏色
 * 2xx: 綠色
 * 4xx/5xx: 紅色
 */
function getStatusColor(status: number): string {
    return status >= 400 ? colors.red : colors.green
}

/**
 * 日誌工具 composable
 * 提供統一格式的 HTTP 請求日誌
 * 使用 console 作為底層輸出，但可以透過環境變數控制日誌等級
 */
export const useLogs = () => {
    // 使用 console 作為 logger
    // 在生產環境可以透過 nuxt.config.ts 的 nitro.logLevel 控制
    const logger = console
    
    /**
     * 記錄 HTTP 請求成功
     * 格式: [時間] 狀態 耗時 方法 URL [快取標記]
     */
    const logSuccess = (
        url: string,
        query: Record<string, any>,
        duration: number,
        status: number,
        method: string = 'GET',
        isFromCache: boolean = false
    ) => {
        const timestamp = formatTimestamp()
        const fullUrl = buildFullUrl(url, query)
        
        // 根據執行時間和狀態碼決定顏色
        const durationColor = getDurationColor(duration)
        const statusColor = getStatusColor(status)
        
        // 組合彩色輸出
        const statusColored = `${statusColor}${status}${colors.reset}`
        const durationColored = `${durationColor}${duration}ms${colors.reset}`
        const methodColored = `${colors.green}${method}${colors.reset}`
        const cacheTag = isFromCache ? `${colors.green}isApiCacheData${colors.reset}` : ''
        
        const message = `${timestamp} ${statusColored} ${durationColored} ${methodColored} ${fullUrl} ${cacheTag}`.trim()
        
        // 使用官方 logger 輸出（會受到環境變數控制）
        logger.log(message)
    }

    /**
     * 記錄 HTTP 請求失敗
     * 格式: [時間] 狀態 耗時 方法 URL
     */
    const logError = (
        url: string,
        query: Record<string, any>,
        duration: number,
        status: number,
        method: string = 'GET',
        message?: string
    ) => {
        const timestamp = formatTimestamp()
        const fullUrl = buildFullUrl(url, query)
        
        // 全部使用紅色
        const statusColored = `${colors.red}${status}${colors.reset}`
        const durationColored = `${colors.red}${duration}ms${colors.reset}`
        const methodColored = `${colors.red}${method}${colors.reset}`
        
        const logMessage = `${timestamp} ${statusColored} ${durationColored} ${methodColored} ${fullUrl}`
        
        // 使用官方 logger 的 error 方法
        logger.error(logMessage)
        
        if (message) {
            logger.error(`  Error: ${message}`)
        }
    }

    /**
     * 記錄一般訊息
     */
    const logInfo = (message: string, data?: any) => {
        const timestamp = formatTimestamp()
        const formattedMessage = `${timestamp} ${colors.cyan}INFO${colors.reset} ${message}`
        
        // 使用官方 logger
        logger.info(formattedMessage, data || '')
    }

    /**
     * 記錄警告訊息
     */
    const logWarning = (message: string, data?: any) => {
        const timestamp = formatTimestamp()
        const formattedMessage = `${timestamp} ${colors.yellow}WARN${colors.reset} ${message}`
        
        // 使用官方 logger
        logger.warn(formattedMessage, data || '')
    }
    
    /**
     * 記錄除錯訊息
     */
    const logDebug = (message: string, data?: any) => {
        const timestamp = formatTimestamp()
        const formattedMessage = `${timestamp} ${colors.cyan}DEBUG${colors.reset} ${message}`
        
        // 使用官方 logger（只在開發環境顯示）
        logger.debug(formattedMessage, data || '')
    }

    return {
        logSuccess,
        logError,
        logInfo,
        logWarning,
        logDebug,
    }
}

