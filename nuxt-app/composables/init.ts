export const ESinit = (data:{ scroll: Function }) => {
    useUpdateViewport()
    const { $LCscroll } = useNuxtApp()
    
    // > For landing animation
    // > Every page should use `pageloaded.value = true` to trigger animation
    // > ------------------------------------------------------------------------

    let isFirstLand = true
    const menuOpen = useMenuOpen()
    const pageloaded = usePageLoaded()

    watch(pageloaded, (next) => {
        if (next && isFirstLand) {
            isFirstLand = false
            document.body.classList.add('is-loaded')

            // Fix static nuxt using lenis-scroll can't get dom
            if (document.querySelector('[data-ssr]')?.dataset.ssr === 'false') {
                const newContainer = document.querySelector('main')
                newContainer !== null ? $LCscroll.addScrollElements(newContainer) : null
            }

            setTimeout(() => {
                document.querySelector('.loading')?.remove()

                setTimeout(() => {
                    if (location.hash) {
                        console.log(decodeURIComponent(location.hash))
                        
                        const el = document.querySelector(decodeURIComponent(location.hash))
                        if (el !== null) {
                            const offsetTop = (el as HTMLElement).offsetTop
                            $LCscroll.scrollTo(offsetTop, { duration: 1, offset: -80 })
                        }
                    }
                }, 1)
            }, 1000)
        }
    }, {
        deep: true,
        immediate: true
    })


    // > Before page transition   
    // > ------------------------------------------------------------------------

    const router = useRouter()

    router.beforeEach((to, from) => {
        if (to.path.replace(/\/$/, '') === from.path.replace(/\/$/, '')) {
            console.log('★★ router beforeEach - same path')
            
            if (to.hash) {
                const el = document.querySelector(decodeURIComponent(to.hash))
                if (el !== null) {
                    const offsetTop = (el as HTMLElement).offsetTop
                    // window.scrollTo(0, 0)
                    $LCscroll.scrollTo(offsetTop, { duration: 1, offset: -80 })
                }
            }

            const oldContainer = document.querySelector('main')
            if (oldContainer !== null) {
                $LCscroll.removeScrollElements(oldContainer)
                document.body.classList.remove('is-loaded')
                document.body.classList.add('is-loading')
            }

            setTimeout(() => {
                const newContainer = document.querySelector('main')
                if (newContainer !== null) {
                    $LCscroll.addScrollElements(newContainer)
                    document.body.classList.add('is-loaded')
                    document.body.classList.remove('is-loading')
                    console.log('★★ page:finish' )
                }
            }, 301);
        } else {
            console.log('★★ router beforeEach')
            pageloaded.value = false
            menuOpen.value = false
            
            const oldContainer = document.querySelector('main')
            if (oldContainer !== null) {
                $LCscroll.removeScrollElements(oldContainer)
                document.body.classList.remove('is-loaded')
                document.body.classList.add('is-loading')
            }

            if (to.hash) {
                setTimeout(() => {
                    const el = document.querySelector(decodeURIComponent(to.hash))
                    
                    if (el !== null) {
                        const offsetTop = (el as HTMLElement).offsetTop
                        
                        $LCscroll.scrollTo(offsetTop, { duration: 1, offset: -80 })
                    }
                }, 1003);
            } else {
                // Make sure lenis scroll to top after page transition
                // 400 depend on vue transition time
                if (document.documentElement.classList.contains('lenis-scrolling')) {
                    setTimeout(() => {
                        $LCscroll.scrollTo(0, { duration: 0.001 })
                        // window.scrollTo(0, 0)
                    }, 200) 
                }
            }
        }
        
    })

    // > After page transition   
    // > ------------------------------------------------------------------------

    const { hook } = useNuxtApp()
    const vueTransitionTime = 200 // leave 0.2 + enter 0.4 = 0.6
    const searchOpen = useSearchOpen()

    hook('page:finish', () => {
        searchOpen.value = false
        if (!isFirstLand) {

            setTimeout(() => {
                const newContainer = document.querySelector('main')
                if (newContainer !== null) {
                    $LCscroll.addScrollElements(newContainer)
                    document.body.classList.add('is-loaded')
                    document.body.classList.remove('is-loading')
                    console.log('★★ page:finish' )
                }
            }, vueTransitionTime + 50)

        } 
    })
    
}

// > Just for fun
// > ------------------------------------------------------------------------

if(process.server) {
    console.log('.                         .-.' );
    console.log(' (_______________________()6 `--,' );
    console.log(' (   __________________   /""""' );
    console.log(' //\\                  //\\' );
    console.log(' \x1b[0m \x1b[33m%s\x1b[0m', '● ES Boilerplate is Awesome - v1.0.0 ●', );
} else {
    console.log("%c🥚 ES Boilerplate 🥚 https://github.com/esdesignstudio/es-boilerplate", "border-left:10px solid #ffe800;border-color:#ffe800;background:#000000;padding:5px 15px;border-radius:5px; color:#ffffff;font-size:10px;")
}