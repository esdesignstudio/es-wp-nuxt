<template>
    <div class="page-index">
        <div class="container">
            HOME
        </div>
        <Footer />
    </div>
</template>
<script setup>
    const pageloaded = usePageLoaded()
    const route = useRoute()
    const { global, page } = usePageData().value
    // const pageData = page[26]

    // if (!pageData) {
    //     throw createError({ statusCode: 404, statusMessage: 'Page Not Found', fatal: true })
    // } else {
    //     pageloaded.value = true
    //     console.log('pageData', pageData);
    // }

    useHead({
        // title: pageData.meta_title ? pageData.meta_title : pageData.og_title,
        // meta: useMetaReturn({
        //     title: pageData.meta_title ? pageData.meta_title : pageData.og_title,
        //     description: pageData.meta_description,
        //     image: pageData.og_image?.url,
        //     url: useRuntimeConfig().public.siteUrl + route.path
        // }),
        // script: [
        //     {
        //         type: 'application/ld+json',
        //         children: useBreadcrumb()
        //     }
        // ]
    })
</script>
<style lang="scss">
    $class-name: page-index;
    .#{$class-name} {

    }
</style>
