export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig()
    const apiUrl = config.public.apiUrl
    const query = getQuery(event)
    const id = query.id
    const type = query.type

    try {
        const response = await fetch(`${apiUrl}/get_collection_${type}?id=${id}`, {
            method: 'GET',
        })
        const data = await response.json()
        
        return data.data
    } catch (error) {
        console.error('獲取Collection Data時出錯：', error)
        throw createError({
            statusCode: 500,
            statusMessage: '無法獲取Collection Data'
        })
    }
})