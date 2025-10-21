<template>
    <div class="page-index">
        <div class="container">
            <h1>
                <span
                    style="display: block;"
                    data-scroll
                    data-scroll-speed="-0.6"
                >HOME</span>
            </h1>
            <pre>
                {{ pageData }}
            </pre>
        </div>
        <Footer />
    </div>
</template>
<script setup>
    const pageloaded = usePageLoaded()
    const route = useRoute()
    const pageData = await getPageData({ collection: 'page', key: '23' })

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
        h1 {
            @include typo('display_40'); 
            color: var(--color-brand-1);
            margin-bottom: 100vh;
        }
    }
</style>
