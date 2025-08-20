#!/bin/bash

# 載入環境變數
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# 設定變數
CONTAINER_NAME="${COMPOSE_PROJECT_NAME}_mysql-maria"
DB_NAME="$WP_MYSQL_DATABASE"
DB_USER="$WP_MYSQL_USERNAME"
DB_PASSWORD="$WP_MYSQL_PASSWORD"
IMPORT_FILE="./db/wp.sql"

# 檢查匯入檔案是否存在
if [ ! -f "$IMPORT_FILE" ]; then
    echo "❌ 找不到匯入檔案: $IMPORT_FILE"
    echo "請確認檔案路徑是否正確"
    exit 1
fi

# 檢查容器是否執行中
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo "❌ MySQL 容器未執行: $CONTAINER_NAME"
    echo "請先啟動 Docker 容器: docker-compose up -d"
    exit 1
fi

# 顯示匯入檔案資訊
echo "📁 匯入檔案: $IMPORT_FILE"
echo "📊 檔案大小: $(du -h "$IMPORT_FILE" | cut -f1)"
echo "🎯 目標資料庫: $DB_NAME"
echo "🐳 目標容器: $CONTAINER_NAME"
echo ""

# 確認匯入
read -p "確定要匯入資料庫嗎？這會覆蓋現有資料 (y/N): " confirm
if [[ ! $confirm =~ ^[Yy]$ ]]; then
    echo "取消匯入"
    exit 0
fi

# 匯入資料庫
echo "開始匯入資料庫..."
docker exec -i "$CONTAINER_NAME" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$IMPORT_FILE"

# 檢查匯入是否成功
if [ $? -eq 0 ]; then
    echo "✅ 資料庫匯入成功"
    echo "🔄 建議重啟應用程式以確保設定生效"
else
    echo "❌ 資料庫匯入失敗"
    exit 1
fi
