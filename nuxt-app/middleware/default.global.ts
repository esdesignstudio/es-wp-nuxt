export default defineNuxtRouteMiddleware((to, from) => {
    const pathMappings = {
        // 301

        //產品
        // '/old': '/new',
    }

    const cleanedPath = decodeURI(to.path).replace(/\/$/, '')
    const newPath = pathMappings[cleanedPath]

    if (newPath) {
        if (to.path.replace(/\/$/, '') !== newPath) {
            return navigateTo(newPath, { redirectCode: 301 })
        }
    } else {
        if (to.path.replace(/\/$/, '') !== to.path && to.path !== '/') {
            return navigateTo(to.path.replace(/\/$/, ''), { redirectCode: 301 })
        }
    }
});