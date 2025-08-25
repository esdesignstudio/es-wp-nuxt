export default defineNuxtRouteMiddleware(async () => {
    // 預先以 getPageData 載入 global 集合，供後續頁面使用
    try {
        await getPageData({ collection: 'global', whole: true, force: false })
    } catch (error) {
        console.error('Failed to load global via getPageData:', error)
    }
})