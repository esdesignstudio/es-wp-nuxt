<template>
    <div class="page-works">
        <pre>
            {{ worksOptions }}
        </pre>
    </div>
</template>
<script setup>
    const worksOptions = await getPageData({ collection: 'works', key: 'works_options' })
    console.log('worksOptions', worksOptions)
</script>
<style lang="scss">
    $class-name: page-works;
    .#{$class-name} {

    }
</style>