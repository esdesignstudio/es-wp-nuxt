import LocomotiveScroll from 'locomotive-scroll'
export default defineNuxtPlugin( nuxtApp => {
    
    const LCscroll = new LocomotiveScroll({
        lenisOptions: {
            lerp: 0.1,
            duration: 1.5,
            orientation: 'vertical',
            gestureOrientation: 'vertical',
            smoothWheel: true,
            smoothTouch: false,
            wheelMultiplier: 1,
            touchMultiplier: 2,
            normalizeWheel: true,
            // easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // https://www.desmos.com/calculator/brs54l4xou
        },
    })

    return {
        provide: {
            LCscroll: LCscroll
        }
    }
})
