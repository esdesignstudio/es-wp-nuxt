#!/bin/sh

# 設定嚴格模式：任何命令失敗就停止執行
set -e

# 錯誤處理函數（簡化版，相容 sh）
handle_error() {
    echo "✕ 安裝失敗！請檢查上方錯誤訊息"
    exit 1
}

echo "🚀 開始安裝程序..."

# 引入.env檔參數
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
    echo "✔ 載入 .env 檔案成功"
else
    echo "✕ 找不到 .env 檔案"
    exit 1
fi

# 檢查是否已定義必要的變數
if [ -z "$COMPOSE_PROJECT_NAME" ] || [ -z "$WP_URL" ] || [ -z "$WP_PORT" ]; then
    echo "未定義必要的變數: COMPOSE_PROJECT_NAME、WP_URL、WP_PORT。請檢查 .env 檔案。"
    exit 1
fi

PRODUCTION_DOMAIN=$(echo "$WP_URL" | sed 's/.*\/\///; s/\/.*//')
if [ -z "$PRODUCTION_DOMAIN" ]; then
    echo "無法取得Domain設定。請檢查 .env 檔案。"
    exit 1
fi

echo "PRODUCTION_DOMAIN = $PRODUCTION_DOMAIN"

# 檢查Docker是否已安裝
echo "🔍 檢查 Docker 安裝狀態..."
if [ -x /usr/bin/docker ]; then
    echo "✔ Docker 已安裝"
else
    echo "📦 Docker 尚未安裝，開始安裝..."
    if sudo sh shell/install/docker_install.sh; then
        echo "✔ Docker 安裝成功"
    else
        echo "✕ Docker 安裝失敗"
        exit 1
    fi
fi

# 檢查Nginx是否已安裝
echo "🔍 檢查 Nginx 安裝狀態..."
if [ -x /usr/sbin/nginx ]; then
    echo "✔ Nginx 已安裝"
else
    echo "🌐 Nginx 尚未安裝，開始安裝..."
    if sudo sh shell/install/nginx_install.sh; then
        echo "✔ Nginx 安裝成功"
    else
        echo "✕ Nginx 安裝失敗"
        exit 1
    fi
fi

# 檢查port是否被佔用
echo "🔍 檢查端口 $WP_PORT 是否可用..."
if [ "$WP_PORT" -ge 1 ] && [ "$WP_PORT" -le 65535 ]; then
    if sudo lsof -Pi :$WP_PORT -sTCP:LISTEN -t >/dev/null; then
        echo "✕ 端口 $WP_PORT 被占用"
        echo "占用端口信息："
        sudo lsof -i :$WP_PORT
        exit 1
    else
        echo "✔ 端口 $WP_PORT 可用"
    fi
else
    echo "✕ 無效的端口: $WP_PORT"
    exit 1
fi

# 建立nginx設定檔
echo "⚙️  建立 Nginx 設定檔..."
if sudo sh shell/install/nginx_setting.sh $COMPOSE_PROJECT_NAME $PRODUCTION_DOMAIN $WP_PORT; then
    echo "✔ Nginx 設定檔建立成功"
else
    echo "✕ Nginx 設定檔建立失敗"
    exit 1
fi

# 修改wp.sql中的連結
echo "🔧 修改 wp.sql 中的連結設定..."
if sudo sh shell/install/repleace_db_url.sh db/wp.sql $WP_URL; then
    echo "✔ wp.sql 連結設定修改成功"
else
    echo "✕ wp.sql 連結設定修改失敗"
    exit 1
fi

# 檢查並建立本機 wp-content/uploads 資料夾
echo "ℹ 檢查 wp-content/uploads 資料夾..."
if [ ! -d "wordpress/wp-content/uploads" ]; then
    echo "建立 wordpress/wp-content/uploads 資料夾..."
    mkdir -p wordpress/wp-content/uploads
    echo "✔ 本機 uploads 資料夾建立成功"
else
    echo "✔ 本機 uploads 資料夾已存在"
fi

# 啟動 Docker 容器
echo "ℹ 啟動 Docker 容器..."
if docker-compose up -d --build; then
    echo "✔ Docker 容器啟動成功"
else
    echo "✕ Docker 容器啟動失敗"
    exit 1
fi

# 等待容器完全啟動
echo "> 等待容器完全啟動..."
sleep 10

# 修改容器內 wp-content/uploads 的資料夾權限
echo "> 設定容器內 uploads 資料夾權限..."
if sh shell/install/wp_permission_setting.sh $COMPOSE_PROJECT_NAME; then
    echo "✔ 容器內資料夾權限設定成功"
else
    echo "✕ 容器內資料夾權限設定失敗"
    exit 1
fi

echo "ℹ 所有安裝步驟完成！"
echo "ℹ Docker 已啟動並執行中"
echo "ℹ 請在安裝完SSL後連線： $WP_URL"