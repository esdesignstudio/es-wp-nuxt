import { readFile } from 'fs/promises'
import { resolve } from 'path'

export default defineEventHandler(async (event) => {
    const type = event.context.params?.type
    const query = getQuery(event)
    const id = query.id
    const slug = query.slug

    if (!type) {
        throw createError({
            statusCode: 400,
            message: '缺少必要的type參數'
        })
    }

    try {
        const filePath = resolve(process.cwd(), 'server/cache', `${type}.json`)
        const fileContent = await readFile(filePath, 'utf-8')
        const jsonData = JSON.parse(fileContent)

        // 如果有提供 id，則返回對應的內容
        if (id && id in jsonData) {
            return jsonData[id]
        }

        // 如果 type 不是 page 且提供了 slug，則通過 slug 查找對應的 key
        if (type !== 'page' && slug) {
            const matchingEntries = Object.entries(jsonData).filter(([_, value]) => 
                value.post && value.post.post_name === slug
            )

            if (matchingEntries.length > 0) {
                // 如果有多個匹配項，按 post_date 排序並返回最新的
                const sortedEntries = matchingEntries.sort((a, b) => 
                    new Date(b[1].post.post_date).getTime() - new Date(a[1].post.post_date).getTime()
                )
                return sortedEntries[0][1]
            }
        }

        // 如果沒有找到對應的內容，則返回整個 JSON 內容
        return jsonData
    } catch (error) {
        console.error(`讀取檔案時發生錯誤：${error}`)
        throw createError({
            statusCode: 404,
            message: '找不到對應的資料'
        })
    }
})
