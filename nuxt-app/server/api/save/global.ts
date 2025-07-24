export default defineEventHandler(async () => {
    const config = useRuntimeConfig()
    const apiUrl = config.public.apiUrl

    try {
        const response = await fetch(`${apiUrl}/get_global`, {
            method: 'GET'
        })
        const data = await response.json()
        
        return data.data.data
    } catch (error) {
        console.error('獲取Global Data時出錯：', error)
        throw createError({
            statusCode: 500,
            statusMessage: '無法獲取Global Data'
        })
    }
})