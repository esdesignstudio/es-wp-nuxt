<template>
    <Teleport to="body">
    <div class="dev" v-if="config.env === 'dev' || config.env === 'uat'">
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
        <div class="dev__switch" :class="{'-active': devSwitch}">
            <div class="dev__switch-button">
                <div class="dev__switch-screensize" v-if="windowSize">
                    <span>螢幕尺寸：</span>{{ windowSize }}
                </div>
                <i></i>
                <span>顯示檢查工具</span>
                <button type="button" :class="{'-active': !devSwitch}" @click="devSwitch = false">OFF</button>
                <button type="button" :class="{'-active': devSwitch}" @click="devSwitch = true">ON</button>
            </div>
        </div>
        <div v-if="devSwitch" class="dev__horiline" ref="devHoriline"></div>
        <div v-if="devSwitch" class="dev__vertline" ref="devVertline"></div>
        <div v-if="devSwitch && textStyles" class="dev__text-info" :style="{ top: mouseY + 'px', left: mouseX + 'px' }">
            <div class="dev__text-info-item">
                <span class="label">Size:</span>
                <span class="value">{{ textStyles.fontSize }}</span>
            </div>
            <div class="dev__text-info-item">
                <span class="label">Weight:</span>
                <span class="value">{{ textStyles.fontWeight }}</span>
            </div>
            <div class="dev__text-info-item">
                <span class="label">LH.:</span>
                <span class="value">{{ textStyles.lineHeight }}</span>
            </div>
            <div class="dev__text-info-item">
                <span class="label">LS.:</span>
                <span class="value">{{ textStyles.letterSpacing }}</span>
            </div>
            <div class="dev__text-info-item">
                <span class="label">Family:</span>
                <span class="value">{{ textStyles.fontFamily }}</span>
            </div>
        </div>
        <div 
            v-if="devSwitch && measureRect" 
            class="dev__measure-rect" 
            :style="{
                left: measureRect.left + 'px',
                top: measureRect.top + 'px',
                width: measureRect.width + 'px',
                height: measureRect.height + 'px'
            }"
        >
            <div class="dev__measure-info">
                {{ measureRect.width }} × {{ measureRect.height }}
            </div>
        </div>
    </div>
    </Teleport>
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
    
    // Horizontal line, vertical line 和文字大小偵測相關
    const devHoriline = ref(null)
    const devVertline = ref(null)
    const textStyles = ref(null)
    const mouseX = ref(0)
    const mouseY = ref(0)
    const windowSize = ref('')
    
    // 測量工具相關
    const isDragging = ref(false)
    const measureRect = ref(null)
    const startPoint = ref({ x: 0, y: 0 })
    
    // 監聽狀態變化並儲存到 cookie
    watch(devSwitch, (newValue) => {
        devSwitchCookie.value = newValue
    })
    
    // 滑鼠跟隨水平線和垂直線功能
    const addCrosshairListener = () => {
        const handleMouseMove = (e) => {
            if (devHoriline.value) {
                devHoriline.value.style.top = e.clientY + 'px'
            }
            if (devVertline.value) {
                devVertline.value.style.left = e.clientX + 'px'
            }
        }
        window.addEventListener('mousemove', handleMouseMove)
        return () => window.removeEventListener('mousemove', handleMouseMove)
    }
    
    // 文字樣式偵測功能
    const calculateTextStyles = (e) => {
        const target = e.target
        const textContent = target.textContent?.trim()
        
        // 檢查是否為 dev__switch 或其子元素
        const isDevSwitch = target.closest('.dev__switch')
        
        if (target.nodeType === Node.ELEMENT_NODE && 
            textContent && 
            textContent.length > 0 &&
            !isDevSwitch) {
            const computedStyle = window.getComputedStyle(target)
            if (computedStyle.fontSize) {
                // 處理 font-family，只顯示第一個字體名稱
                const fontFamily = computedStyle.fontFamily.split(',')[0].replace(/['"]/g, '').trim()
                
                textStyles.value = {
                    fontSize: computedStyle.fontSize,
                    fontWeight: computedStyle.fontWeight,
                    lineHeight: computedStyle.lineHeight,
                    letterSpacing: computedStyle.letterSpacing === 'normal' ? '0px' : computedStyle.letterSpacing,
                    fontFamily: fontFamily
                }
                mouseX.value = e.clientX + 10 // 稍微偏移避免遮擋
                mouseY.value = e.clientY - 20
            }
        } else {
            textStyles.value = null
        }
    }
    
    // 測量工具拖曳功能
    const handleMeasureMouseDown = (e) => {
        if (!devSwitch.value) return
        
        // 清除已存在的方框，開始新的測量
        measureRect.value = null
        
        isDragging.value = true
        startPoint.value = {
            x: e.clientX,
            y: e.clientY
        }
        
        measureRect.value = {
            left: e.clientX,
            top: e.clientY,
            width: 0,
            height: 0
        }
        
        e.preventDefault()
    }
    
    const handleMeasureMouseMove = (e) => {
        if (!devSwitch.value || !isDragging.value) return
        
        const currentX = e.clientX
        const currentY = e.clientY
        const width = Math.abs(currentX - startPoint.value.x)
        const height = Math.abs(currentY - startPoint.value.y)
        
        measureRect.value = {
            left: Math.min(startPoint.value.x, currentX),
            top: Math.min(startPoint.value.y, currentY),
            width,
            height
        }
    }
    
    const handleMeasureMouseUp = (e) => {
        if (!devSwitch.value) return
        
        isDragging.value = false
        // 不再在 mouseup 時清除方框，讓它保持顯示
    }
    
    // 處理點擊清除方框
    const handleMeasureClick = (e) => {
        if (!devSwitch.value) return
        
        // 檢查是否為單純點擊（沒有拖曳）
        const isClick = startPoint.value && 
                       Math.abs(e.clientX - startPoint.value.x) < 5 && 
                       Math.abs(e.clientY - startPoint.value.y) < 5
        
        // 如果是單純點擊且有方框存在，則清除方框
        if (isClick && measureRect.value && 
            (measureRect.value.width < 10 && measureRect.value.height < 10)) {
            measureRect.value = null
        }
    }
    
    // 清理函數
    let removeCrosshairListener = null
    let removeTextSizeListener = null
    let removeMeasureListeners = null
    
    // 添加測量工具事件監聽器
    const addMeasureListeners = () => {
        window.addEventListener('mousedown', handleMeasureMouseDown)
        window.addEventListener('mousemove', handleMeasureMouseMove)
        window.addEventListener('mouseup', handleMeasureMouseUp)
        window.addEventListener('click', handleMeasureClick)
        
        return () => {
            window.removeEventListener('mousedown', handleMeasureMouseDown)
            window.removeEventListener('mousemove', handleMeasureMouseMove)
            window.removeEventListener('mouseup', handleMeasureMouseUp)
            window.removeEventListener('click', handleMeasureClick)
        }
    }
    
    // 添加/移除事件監聽器
    const toggleEventListeners = (enable) => {
        if (enable) {
            removeCrosshairListener = addCrosshairListener()
            removeMeasureListeners = addMeasureListeners()
            window.addEventListener('mousemove', calculateTextStyles)
            removeTextSizeListener = () => window.removeEventListener('mousemove', calculateTextStyles)
        } else {
            removeCrosshairListener?.()
            removeMeasureListeners?.()
            removeTextSizeListener?.()
            removeCrosshairListener = null
            removeMeasureListeners = null
            removeTextSizeListener = null
            textStyles.value = null
            isDragging.value = false
            measureRect.value = null
        }
    }
    
    // 監聽開關狀態變化
    watch(devSwitch, (newVal) => {
        toggleEventListeners(newVal)
    })
    
    onMounted(() => {
        // 快速鍵開啟
        if (config.env === 'dev' || config.env === 'uat') { 
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

            // 監聽視窗大小變化
            const handleResize = () => {
                windowSize.value = `${window.innerWidth} x ${window.innerHeight}`
            }
            handleResize()
            window.addEventListener('resize', handleResize)
            
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
        --color-dev-line: #00ff48cc;

        &__wrap {
            height: 100vh;
            position: fixed;
            flex-direction: column;
            top: 0;
            left: 50%;
            z-index: 10001;
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
            background: var(--color-dev-line);
            z-index: 998;
            pointer-events: none;
            top: 0;
        }

        &__vertline {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 1px;
            background: var(--color-dev-line);
            z-index: 998;
            pointer-events: none;
            left: 0;
        }

        &__text-info {
            position: fixed;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-family: monospace;
            z-index: 1000;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 100px;

            &-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 4px;
                
                &:last-child {
                    margin-bottom: 0;
                }

                .label {
                    color: rgba(255, 255, 255, 0.7);
                    margin-right: 12px;
                    font-weight: normal;
                }

                .value {
                    // color: #00ff48;
                    font-weight: bold;
                    text-align: right;
                    flex-shrink: 0;
                    max-width: 120px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }
        }

        &__measure-rect {
            position: fixed;
            border: 2px solid var(--color-dev-line);
            background: rgba(0, 255, 72, 0.1);
            z-index: 997;
        }

        &__measure-info {
            position: absolute;
            top: -25px;
            left: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-family: monospace;
            white-space: nowrap;
            font-weight: bold;
        }

        &__switch {
            z-index: 5555555555;
            left: 10px;
            bottom: 10px;
            overflow: hidden;
            position: fixed;

            @media (max-width: 768px) {
                left: 50%;
                margin-left: -170px;
            }

            &-button {
                height: 30px;
                font-size: 14px;
                border-radius: 50px;
                color: rgba(255,255,255,.8);
                background: #000000;
                padding: 10px 24px;
                display: flex;
                align-items: center;
                gap: 1rem;
                backdrop-filter: blur(10px);
                box-shadow: 2px 2px 8px rgba(0,0,0,0.5);
                transition: transform .3s;
                transform: translateY(130%);
                font-weight: bolder;

                span {
                    opacity: .8;
                    font-weight: normal;
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

            &.-active {
                .dev__switch-button {
                    transform: translateY(0);
                }
            }
            @media (min-width: 768px) {
                &:hover {
                    .dev__switch-button {
                        transform: translateY(0);
                    }
                }
            }
        }
    }
</style>