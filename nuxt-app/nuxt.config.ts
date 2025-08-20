// 讀取根目錄的環境變數
import { readFileSync } from 'fs'
import { resolve, join } from 'path'

// 使用根目錄的 .env 檔案寫進去 process.env
const rootEnvPath = resolve(process.cwd(), '../.env')
try {
  const envContent = readFileSync(rootEnvPath, 'utf8')
  envContent.split('\n').forEach(line => {
    const [key, value] = line.split('=')
    if (key && value && !process.env[key]) {
      process.env[key] = value.replace(/^["']|["']$/g, '')
    }
  })
} catch (error) {
  console.warn('無法讀取根目錄的 .env 檔案:', error instanceof Error ? error.message : String(error))
}

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
                { name: 'theme-color', content: '#672146' },
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
        },
    
        server: { // 解決開發時 websocket 問題
            // hmr: {
            //     protocol: 'ws',
            //     host: 'localhost'
            // },
            allowedHosts: [
              'host.docker.internal'
            ]
        },
        plugins: [
            // 解決 nuxt-icons 圖示問題
            {
                name: 'vite-plugin-glob-transform',
                transform(code: string, id: string) {
                    if (id.includes('nuxt-icons')) {
                    return code.replace(/as:\s*['"]raw['"]/g, 'query: "?raw", import: "default"');
                    }
                    return code;
                }
            }
        ]
    },

    modules: [
        '@nuxt/devtools',
        '@nuxtjs/sitemap',
        '@nuxt/image',
        'nuxt-icons'
    ],

    runtimeConfig: {
        public: {
            env: process.env.ENV,
            siteUrl: process.env.NUXT_SITE_URL,
            apiUrl: process.env.WP_URL + '/wp-json/api',
            apiWpUrl: process.env.WP_URL + '/wp-json/wp/v2'
        },
    },

    devtools: {
        enabled: process.env.ENV === 'dev',
    },

    sitemap: {
        sources: [ `${process.env.WP_URL}/wp-json/api/get_sitemap` ],
        includeAppSources: true,
    }
})