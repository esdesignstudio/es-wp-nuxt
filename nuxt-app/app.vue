<template>
    <main data-scroll-container>
        <Header />
        <NuxtPage />
    </main>
    <Dev/>
</template>
<script setup>
    const config = useRuntimeConfig()
    const { data: global } = await useAsyncData('getGlobalData', async () => {
        const res = await $fetch(`${config.public.apiUrl}/get_global`)
        return res.data
    })
    useGlobal().value = global.value
    
    onMounted(() => {
        ESinit()
    })

    useHead({
        // title: global.title,
        meta: [
            { name: 'theme-color', content: '#852A26' },
        ],
    })
</script>
<style lang="scss">
#es-app {
    
}
</style>