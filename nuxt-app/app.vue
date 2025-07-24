<template>
    <main data-scroll-container>
        <Header />
        <NuxtPage />
    </main>
    <Dev/>
</template>
<script setup>
    const { global } = usePageData().value
    
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