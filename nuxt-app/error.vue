<template>
    <div class="error-page">
        <Header />
        <main data-scroll-container>
            <div class="container">
                <div class="error-page__wrap">
                    <ClientOnly>
                        <h1>
                            {{ error.statusCode }}<br>
                            {{ error.message.split(':')[0] }}
                        </h1>
                    </ClientOnly>
                </div>
            </div>
        </main>
        <Footer />
    </div>
</template>
<script setup>
    const pageloaded = usePageLoaded()
    const config = useRuntimeConfig().public
    const { global } = usePageData().value

    const props = defineProps({
        error: {
            type: Object,
            required: true
        }
    })

    onMounted(() => {
        nextTick(() => {
            pageloaded.value = true;
            console.error('errorPage', props.error)
        })
    })
    useHead({
        title: props.error.statusCode,
        meta: [
            {
                hid: props.error.statusMessage,
                name: props.error.statusMessage,
                content: props.error.message.split(':')[0]
            }
        ]
    })
</script>
<style lang="scss">
    $class-name: error-page;
    .#{$class-name} {
        h1 {
        }
    }
</style>