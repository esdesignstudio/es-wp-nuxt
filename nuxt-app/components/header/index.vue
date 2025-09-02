<template>
    <header class="header">
        <nav class="container">
            HEADER
            <ul>
                <li v-for="(item, key) in global?.header?.link" :key="key">
                    <NuxtLink :to="item?.url">{{ item?.name }}</NuxtLink>
                </li>
            </ul>
        </nav>
    </header>
</template>
<script setup>
    const config = useRuntimeConfig().public
    const global = useGlobal()
    const route = useRoute()
    const searchOpen = useSearchOpen()
    const menuOpen = useMenuOpen()
</script>
<style lang="scss">
$class-name: header;
.#{$class-name} {
    nav {
        display: flex;
        justify-content: space-between;
    }
}
</style>