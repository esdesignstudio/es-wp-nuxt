<template>
    <div class="page-index">
        <div class="container">
            <h1>
                HOME {{ pageData.title }}
            </h1>
        </div>
        <Footer />
    </div>
</template>
<script setup>
    const pageloaded = usePageLoaded()
    const route = useRoute()
    const config = useRuntimeConfig()
    // 使用新的 cache/fetch API
    const { data: pageData } = await useAsyncData('getHomePageData', async () => {
        const res = await $fetch('/api/cache/fetch', {
            method: 'POST',
            body: {
                apiUrl: `${config.public.apiUrl}/get_page_custom?slug=index`,
                method: 'GET',
                cache: true
            }
        })
        return res.data
    })

    // if (!pageData) {
    //     throw createError({ statusCode: 404, statusMessage: 'Page Not Found', fatal: true })
    // } else {
    //     pageloaded.value = true
    //     console.log('pageData', pageData);
    // }

    useHead({
        title: pageData.value.meta_title,
        // meta: useMetaReturn({
        //     title: pageData.value.meta_title ? pageData.value.meta_title : pageData.value.og_title,
        //     description: pageData.value.meta_description,
        //     image: pageData.value.og_image?.url,
        //     url: useRuntimeConfig().public.siteUrl + route.path
        // })
    })
</script>
<style lang="scss">
    $class-name: page-index;
    .#{$class-name} {
        h1 {
            @include typo('display_40'); 
            color: var(--color-brand-1);
            margin-bottom: 100vh;
        }
    }
</style>
