#!/bin/bash

# 檢查是否提供了檔案路徑和替換字串作為參數
if [ $# -ne 2 ]; then
    echo "repleace_db_rul.sh fail - 請提供2個參數：檔案路徑和要替換的字串"
    exit 1
fi

file_path=$1
replace_string=$2

# 使用 sed 命令替換檔案中的指定字串，加上 http:// 字串
sed -i "s|http\?://localhost:9000|$replace_string|g; s|http\?://localhost:3000|$replace_string|g" "$file_path"

echo "已將 $file_path 中的 http://localhost:9000 和 http://localhost:3000 全部替換成 $replace_string"