export const esNotification = (
        data: {
            type: string, // 樣式寫在 /plugin/tools.scss
            title: string, 
            message: string
        }
    ) => {

    if (document.querySelector('.esNotification')) {return false}

    const div = document.createElement('div') as HTMLElement
    div.innerHTML = '<div class="inner"><h2>' + data.title + '</h2><p>' + data.message + '</p></div>'
    div.classList.add('esNotification')
    div.classList.add(data.type)
    document.body.appendChild(div)

    setTimeout(() => {
        const noti = document.querySelector('.esNotification') as HTMLElement
        noti.classList.add('out')
        setTimeout(() => {noti.remove()}, 200)
    }, 4000)
}

export const esShareTo = ( 
    data:{
        url: string, 
        socialMedia: string 
    }
) => {
    const viewport = useViewport()
    if (viewport.value.isMobile) {
        if (navigator.share) {
            navigator.share({
                title: document.title,
                text: document.title,
                url: data.url
            })
        }
    } else {
        let shareUrl
        switch (data.socialMedia) {
            case 'Facebook' :
                shareUrl = 'https://facebook.com/sharer.php?u=' + data.url
                break
            case 'Line' :
                shareUrl = 'https://social-plugins.line.me/lineit/share?url=' + data.url + '&amp;via=esdesign'
                break
            case 'Twitter' :
                shareUrl = 'https://twitter.com/intent/tweet?url=' + data.url + '&amp;via=esdesign'
                break
            case 'Instagram' :
                // 只能分享到 IG Story，且僅限手機瀏覽器
                shareUrl = 'https://www.instagram.com/stories/share/?url=' + data.url
                break
        }

        window.open(
            shareUrl, 
            'shareWindow',
            'menubar=0,location=0,toolbar=0,status=0,scrollbars=1,width=800,height=600'
        )
    }
}

export const esCopyToClipboard = (url: string) => {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url)
    } else {
        const textarea = document.createElement('textarea')
        textarea.value = url
        document.body.appendChild(textarea)
        textarea.select()
    }
}
