export default defineNuxtConfig({
    app: {
        rootId: 'es-app',
        pageTransition: { name: 'page', mode: 'out-in' },
        head: {
            charset: 'utf-8',
            // titleTemplate: '%s ✷ ' + process.env.APP_NAME,
            meta: [
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { name: 'author', content: 'Web developer ES Design' },
                { property: 'og:type', content: 'website' },
                { property: 'og:image', content: '/socialshare.jpg' },
                { name: 'robots', content: process.env.ENV === 'prod' ? 'index, follow' : 'noindex, nofollow' }
            ],
            link: [
                { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: 'anonymous' },
                { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Noto+Sans+TC&display=swap' },
                { rel: 'stylesheet', href: 'https://use.typekit.net/qpp0iio.css' }
            ],
            noscript: [
                { innerHTML: '<style>text{position:fixed;top:0;left:0;width:100vw;height:100vh;font-size:2rem;background-color:#000;color:#fff;z-index:10000;display:flex;align-items:center;justify-content:center;text-align:center;padding:5rem}</style>' },
                { innerHTML: '😓：Sorry your JavaScript is off or your browser does not support JavaScript 😓' }
            ], 
            script: [
                // { src: ''}
            ]
        }
    },

    css: [  '~/assets/scss/main.scss' ],

    vite: {
        css: {
            preprocessorOptions: {
            scss: {
                additionalData: '@use "@/assets/scss/mixins/mixin.scss" as *;'
            }
            }
        }
    },

    modules: [
        '@nuxt/devtools',
        '@nuxtjs/sitemap',
        '@nuxt/image'
    ],

    runtimeConfig: {
        public: {
            env: process.env.ENV,
            siteUrl: process.env.SITE_URL,
            apiUrl: process.env.API_URL + '/wp-json/api',
            apiWpUrl: process.env.API_URL + '/wp-json/wp/v2',
            siteName: process.env.APP_NAME
        },
    },

    devtools: {
        enabled: process.env.ENV === 'dev',
    },

    sitemap: {
        sources: [ `${process.env.API_URL}/wp-json/api/get_sitemap` ],
        includeAppSources: true,
    }
})