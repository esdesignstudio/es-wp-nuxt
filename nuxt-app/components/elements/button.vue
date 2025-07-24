<template>
    <NuxtLink
        class="elements-button click"
        :class="`-color-${color} -border-${borderColor} -background-${backgroundColor} -hover-color-${hoverColor} -hover-border-${hoverBorderColor} -hover-background-${hoverBackgroundColor}` + (disabled ? ' -disabled' : '') + (withicon ? ' -withicon' : '')"
        :to="props.data.link"
        :target="props.data.target"
    >
        <div class="elements-button__text">
            <slot></slot>
        </div>
    </NuxtLink>
</template>
<script setup>
    const props = defineProps({
        data: {
            type: Object,
            default: {
                link: '',
                target: '',
            },
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        color: {
            type: String,
            default: 'black-1',
        },
        borderColor: {
            type: String,
            default: 'transparent',
        },
        backgroundColor: {
            type: String,
            default: 'brand-2'
        },
        hoverColor: {
            type: String,
            default: 'white-1',
        },
        hoverBorderColor: {
            type: String,
            default: 'black-1',
        },
        hoverBackgroundColor: {
            type: String,
            default: 'brand-1',
        },
        withicon: {
            type: Boolean,
            default: false,
        },
    })
</script>
<style lang="scss">
$class-name: elements-button;
.#{$class-name} {

    
}
</style>