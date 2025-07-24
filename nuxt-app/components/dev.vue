<template>
    <div class="dev" v-if="config.env === 'dev'">
        <div 
            class="dev__wrap container"
            v-if="devSwitch"
        >
            <div class="grid">
                <div v-for="num in 12" :key="num" />
                <div v-for="num in 8" :key="num" />
                <div v-for="num in 4" :key="num" />
            </div>
        </div>
        <div class="dev__switch">
            <span>Column 顯示</span>
            <i></i>
            <button type="button" :class="{'-active': !devSwitch}" @click="devSwitch = false">OFF</button>
            <button type="button" :class="{'-active': devSwitch}" @click="devSwitch = true">ON</button>
        </div>
        <div v-if="devSwitch" class="dev__horiline" ref="devHoriline"></div>
        <div v-if="devSwitch && textWidth" class="dev__text-size" :style="{ top: mouseY + 'px', left: mouseX + 'px' }">
            {{ textWidth }}
        </div>
    </div>
</template>
<script setup>
    // 從 cookie 讀取狀態，沒有則預設為 false
    const devSwitchCookie = useCookie('devSwitch', {
        default: () => false,
        serialize: JSON.stringify,
        deserialize: JSON.parse
    })
    
    const devSwitch = ref(devSwitchCookie.value)
    const config = useRuntimeConfig().public
    
    // Horizontal line 和文字大小偵測相關
    const devHoriline = ref(null)
    const textWidth = ref(null)
    const mouseX = ref(0)
    const mouseY = ref(0)
    
    // 監聽狀態變化並儲存到 cookie
    watch(devSwitch, (newValue) => {
        devSwitchCookie.value = newValue
    })
    
    // 滑鼠跟隨水平線功能
    const addHorizontalLineListener = () => {
        const handleMouseMove = (e) => {
            if (devHoriline.value) {
                devHoriline.value.style.top = e.clientY + 'px'
            }
        }
        window.addEventListener('mousemove', handleMouseMove)
        return () => window.removeEventListener('mousemove', handleMouseMove)
    }
    
    // 文字大小偵測功能
    const calculateTextFontSize = (e) => {
        const target = e.target
        const textContent = target.textContent?.trim()
        
        if (target.nodeType === Node.ELEMENT_NODE && 
            textContent && 
            textContent.length > 0) {
            const computedStyle = window.getComputedStyle(target)
            if (computedStyle.fontSize) {
                textWidth.value = computedStyle.fontSize
                mouseX.value = e.clientX + 10 // 稍微偏移避免遮擋
                mouseY.value = e.clientY - 20
            }
        } else {
            textWidth.value = null
        }
    }
    
    // 清理函數
    let removeHorilineListener = null
    let removeTextSizeListener = null
    
    // 添加/移除事件監聽器
    const toggleEventListeners = (enable) => {
        if (enable) {
            removeHorilineListener = addHorizontalLineListener()
            window.addEventListener('mousemove', calculateTextFontSize)
            removeTextSizeListener = () => window.removeEventListener('mousemove', calculateTextFontSize)
        } else {
            removeHorilineListener?.()
            removeTextSizeListener?.()
            removeHorilineListener = null
            removeTextSizeListener = null
            textWidth.value = null
        }
    }
    
    // 監聽開關狀態變化
    watch(devSwitch, (newVal) => {
        toggleEventListeners(newVal)
    })
    
    onMounted(() => {
        // 快速鍵開啟
        if (config.env === 'dev') { 
            console.log("%cShift + Option + C Toggle DevColumns", "border-left:10px solid #ffe800;border-color:#ffe800;background:#000000;padding:5px 15px;border-radius:5px; color:#ffffff;font-size:10px;");
            
            // 鍵盤快速鍵
            const handleKeydown = (e) => {
                if (e.shiftKey && e.altKey && e.code === 'KeyC') {
                    devSwitch.value = !devSwitch.value
                }
            }
            window.addEventListener('keydown', handleKeydown)
            
            // 如果初始狀態為開啟，則啟動監聽器
            if (devSwitch.value) {
                nextTick(() => {
                    toggleEventListeners(true)
                })
            }
            
            // 清理函數
            onBeforeUnmount(() => {
                window.removeEventListener('keydown', handleKeydown)
                toggleEventListeners(false)
            })
        }
    })
</script>
<style lang="scss">
$col-opacity: .1;
    .dev {
        &__wrap {
            height: 100vh;
            position: fixed;
            flex-direction: column;
            top: 0;
            left: 50%;
            z-index: 999;
            transform: translateX(-50%);
            pointer-events: none;
            display: flex;
        
            .grid > div {
                @include size(100%, 100vh);
                // @include editor();
                border-left: 1px solid rgba(0,0,0,$col-opacity);
                border-right: 1px solid rgba(0,0,0,$col-opacity);
                
                @include media-breakpoint-down(tablet) {
                
                }
            }
        }

        &__horiline {
            position: fixed;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 0, 0, 0.8);
            z-index: 998;
            pointer-events: none;
            top: 0;
        }

        &__text-size {
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            font-family: monospace;
            z-index: 1000;
            pointer-events: none;
            white-space: nowrap;
        }

        &__switch {
            z-index: 999;
            height: 30px;
            left: 10px;
            bottom: 10px;
            font-size: 0.7em;
            border-radius: 50px;
            color: rgba(255,255,255,.8);
            position: fixed;
            background: #000000;
            padding:5px 15px;
            display: flex;
            align-items: center;
            gap: 1rem;
            backdrop-filter: blur(10px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.5);

            > span {
                font-size: .7em;
            }

            > i {
                border-left: 1px solid rgba(136, 136, 136, 0.4);
                width: 1px;
                height: 10px;
            }

            > button {
                opacity: .5;
                
                &.-active {
                    opacity: 1;
                }
            }
        }
    }
</style>