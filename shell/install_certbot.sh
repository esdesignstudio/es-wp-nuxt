#!/bin/sh

# 設定嚴格模式：任何命令失敗就停止執行
set -e

echo "🔐 開始 SSL 憑證安裝程序..."

# 引入.env檔參數
if [ -f .env ]; then
    export $(cat .env | grep -v '#' | awk '/=/ {print $1}')
    echo "✔ 載入 .env 檔案成功"
else
    echo "✕ 找不到 .env 檔案"
    exit 1
fi

# 檢查是否已定義必要的變數
if [ -z "$WP_URL" ] || [ -z "$COMPOSE_PROJECT_NAME" ]; then
    echo "✕ 未定義必要的變數 WP_URL 或 COMPOSE_PROJECT_NAME。請檢查 .env 檔案。"
    exit 1
fi

# 從 WP_URL 提取域名
DOMAIN=$(echo "$WP_URL" | sed 's|https\?://||' | sed 's|/.*||')
if [ -z "$DOMAIN" ]; then
    echo "✕ 無法從 WP_URL 提取域名。請檢查 .env 檔案中的 WP_URL 格式。"
    exit 1
fi

echo "🌐 目標域名: $DOMAIN"

# 檢詢使用者確認
echo ""
echo "ℹ 即將執行以下操作:"
echo "   1. 安裝/更新 Snapd 和 Certbot"
echo "   2. 為域名 $DOMAIN 申請 SSL 憑證"
echo "   3. 自動修改 Nginx 設定"
echo "   4. 設定自動更新憑證"
echo ""
read -p "確定要繼續嗎？(y/N): " confirm
if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
    echo "✕ 已取消安裝"
    exit 0
fi

# 檢查 Nginx 是否正在執行
if ! systemctl is-active --quiet nginx; then
    echo "ℹ Nginx 未執行，嘗試啟動..."
    if sudo systemctl start nginx; then
        echo "✔ Nginx 啟動成功"
    else
        echo "✕ Nginx 啟動失敗，請檢查設定"
        exit 1
    fi
fi

# 檢查設定檔衝突
echo "🔧 檢查 Nginx 設定檔..."
if [ -f "/etc/nginx/sites-enabled/default" ]; then
    echo "ℹ 發現預設設定檔存在"
    # 檢查 default 檔案是否包含相同域名
    if sudo grep -q "server_name.*$DOMAIN" /etc/nginx/sites-enabled/default 2>/dev/null; then
        echo "ℹ 預設設定檔包含相同域名，建議移除避免衝突"
        read -p "是否移除預設設定檔？(y/N): " remove_default
        if [ "$remove_default" = "y" ] || [ "$remove_default" = "Y" ]; then
            sudo rm -f /etc/nginx/sites-enabled/default
            sudo systemctl reload nginx
            echo "✔ 預設設定檔已移除"
        fi
    else
        echo "✔ 預設設定檔不會造成衝突"
    fi
fi

# 步驟 1: 更新 snap
echo "📦 更新 Snapd..."
if sudo snap install core; sudo snap refresh core; then
    echo "✔ Snapd 更新成功"
else
    echo "✕ Snapd 更新失敗"
    exit 1
fi

# 步驟 2: 安裝 Certbot
echo "🔧 安裝 Certbot..."
if sudo snap install --classic certbot; then
    echo "✔ Certbot 安裝成功"
else
    echo "✕ Certbot 安裝失敗"
    exit 1
fi

# 步驟 3: 設定系統直接使用 certbot 指令
echo "🔗 設定 Certbot 系統連結..."
if sudo ln -sf /snap/bin/certbot /usr/bin/certbot; then
    echo "✔ Certbot 系統連結設定成功"
else
    echo "✕ Certbot 系統連結設定失敗"
    exit 1
fi

# 步驟 4: 用 Certbot 申請憑證並修改 Nginx 設定
echo "📜 申請 SSL 憑證並設定 Nginx..."
echo "ℹ 接下來需要輸入 Email 並回答幾個問題"
echo ""

# 確認專案的 Nginx 設定檔存在
PROJECT_CONFIG="/etc/nginx/sites-enabled/$COMPOSE_PROJECT_NAME"
if [ ! -f "$PROJECT_CONFIG" ]; then
    echo "✕ 找不到專案的 Nginx 設定檔: $PROJECT_CONFIG"
    echo "請先執行 shell/install.sh 建立 Nginx 設定"
    exit 1
fi

echo "ℹ 使用專案設定檔: $PROJECT_CONFIG"

if sudo certbot --nginx -d "$DOMAIN"; then
    echo "✔ SSL 憑證申請成功，Nginx 設定已自動更新"
    echo "ℹ 設定檔案位置: $PROJECT_CONFIG"
else
    echo "✕ SSL 憑證申請失敗"
    exit 1
fi

# 步驟 5: 設定自動更新憑證
echo "⏰ 設定憑證自動更新..."

# 檢查是否已存在自動更新設定
if sudo crontab -l 2>/dev/null | grep -q "certbot renew"; then
    echo "✔ 憑證自動更新已設定"
else
    echo "📅 添加憑證自動更新到 Crontab..."
    # 將現有的 crontab 和新的任務合併
    (sudo crontab -l 2>/dev/null; echo "0 0 1 * * certbot renew --quiet") | sudo crontab -
    if [ $? -eq 0 ]; then
        echo "✔ 憑證自動更新設定成功（每月 1 號執行）"
    else
        echo "✕ 憑證自動更新設定失敗"
        exit 1
    fi
fi

# 測試憑證更新
echo "🧪 測試憑證更新功能..."
if sudo certbot renew --dry-run; then
    echo "✔ 憑證更新測試成功"
else
    echo "✕ 憑證更新測試失敗"
    exit 1
fi

echo ""
echo "🎉 SSL 憑證安裝完成！"
echo "🔒 您的網站現在可以透過 HTTPS 訪問: https://$DOMAIN"
echo "📱 自動更新已設定，憑證將在每月 1 號自動更新"
echo ""
echo "ℹ 後續步驟："
echo "   - 確認網站可以透過 HTTPS 正常訪問"
echo "   - 如有需要，更新應用程式中的 URL 設定"
