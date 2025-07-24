export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig()
    const apiUrl = config.public.apiUrl
    const query = getQuery(event)
    const id = query.id

    if (!id) {
        throw createError({
            statusCode: 400,
            statusMessage: '缺少必要的 id 參數'
        })
    }

    try {
        const response = await fetch(`${apiUrl}/get_page_custom?id=${id}`, {
            method: 'GET',
        })
        const data = await response.json()
        return data.data
    } catch (error) {
        console.error('獲取Page Data時出錯：', error)
        throw createError({
            statusCode: 500,
            statusMessage: '無法獲取Page Data'
        })
    }
})