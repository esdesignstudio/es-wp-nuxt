export default defineNuxtRouteMiddleware(async () => {
    const pageData = usePageData()

    const { data: cacheList } = await useFetch('/api/read/list')

    if (cacheList.value.files) {
        for (const cache of cacheList.value.files) {
            if (!pageData.value[cache]) {
                const { data: cacheData } = await useFetch(`/api/read/${cache}`)
                pageData.value[cache] = cacheData.value
            }
        }
    }
})