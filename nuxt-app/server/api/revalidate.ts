import fs from 'fs';
import path from 'path';

export default defineEventHandler(async (event) => {
    const body = await readBody(event)
    const { id, type, slug, action } = body

    console.log(`收到重新驗證請求：${type} ${id} ${action || '更新'}`)

    // 建立 cache 目錄路徑
    const cacheDir = path.join(process.cwd(), 'server', 'cache')
    
    // 確保 cache 目錄存在
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true })
    }

    // 建立檔案路徑
    const filePath = path.join(cacheDir, `${type}.json`)

    // 如果是刪除操作
    if (action === 'delete') {
        if (fs.existsSync(filePath)) {
            let fileContent = {}
            const fileData = fs.readFileSync(filePath, 'utf-8')
            fileContent = JSON.parse(fileData)
            
            // 刪除指定的項目
            if (type === 'global') {
                fileContent = {}
            } else {
                // 找出所有相關的 ID
                const idsToDelete = new Set()
                Object.entries(fileContent).forEach(([key, value]: [string, any]) => {
                    const languages = Object.values(value)
                    languages.forEach((langData: any) => {
                        if (langData?.post?.ID === parseInt(id)) {
                            idsToDelete.add(key)
                        }
                    })
                })

                // 刪除所有相關的項目
                idsToDelete.forEach(idToDelete => {
                    delete fileContent[idToDelete]
                    console.log(`已從快取中刪除關聯ID：${idToDelete}`)
                })
            }
            
            // 寫入更新後的內容到檔案
            fs.writeFileSync(filePath, JSON.stringify(fileContent, null, 2))
            console.log(`已從快取中刪除：${type} ${id}`)
            return { success: true }
        }
        return { success: true }
    }

    // 根據 type 決定 API 端點
    let apiUrl
    if (type === 'page') {
        apiUrl = `/api/save/page?id=${id}`
    } else if (type === 'global') {
        apiUrl = `/api/save/global`
    } else {
        apiUrl = `/api/save/collection?type=${type}&id=${id}`
    }

    // 從 API 獲取資料
    const response = await $fetch(apiUrl)

    // 讀取現有的 JSON 檔案內容（如果存在）
    let fileContent = {}
    if (fs.existsSync(filePath)) {
        const fileData = fs.readFileSync(filePath, 'utf-8')
        fileContent = JSON.parse(fileData)
    }

    // 更新檔案內容
    if (type === 'global') {
        fileContent = response
    } else {
        fileContent[id] = response
    }

    // 寫入更新後的內容到檔案
    fs.writeFileSync(filePath, JSON.stringify(fileContent, null, 2))
    console.log(`已更新檔案：${filePath}`)

    return { success: true }
})
