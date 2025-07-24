<template>
    <header class="header">
        <nav class="container">
            HEADER
        </nav>
    </header>
</template>
<script setup>
    const config = useRuntimeConfig().public
    const { global } = usePageData().value
    const route = useRoute()
    const searchOpen = useSearchOpen()
    const menuOpen = useMenuOpen()
</script>
<style lang="scss">
$class-name: header;
.#{$class-name} {


}
</style>