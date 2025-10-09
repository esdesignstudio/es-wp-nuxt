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
BACKUP_DIR="./db/backup"
EXPORT_FILE="./db/wp.sql"

# 建立備份目錄（如果不存在）
mkdir -p "$BACKUP_DIR"

# 如果現有的 wp.sql 存在，先備份
if [ -f "$EXPORT_FILE" ]; then
    BACKUP_DATE=$(date +%Y%m%d-%H%M)
    BACKUP_FILE="$BACKUP_DIR/wp-backup-$BACKUP_DATE.sql"
    echo "> 備份現有資料庫檔案: $BACKUP_FILE"
    mv "$EXPORT_FILE" "$BACKUP_FILE"
fi

# 匯出資料庫
echo "> 開始匯出資料庫: $DB_NAME"
docker exec "$CONTAINER_NAME" mysqldump -u"$DB_USER" -p"$DB_PASSWORD" --skip-comments --single-transaction --routines --triggers "$DB_NAME" | grep -v "^/\*M!" > "$EXPORT_FILE"

# 檢查匯出是否成功
if [ $? -eq 0 ]; then
    echo "✔ 資料庫匯出成功: $EXPORT_FILE"
    echo "ℹ 檔案大小: $(du -h "$EXPORT_FILE" | cut -f1)"
else
    echo "✕ 資料庫匯出失敗"
    exit 1
fi
