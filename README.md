# ES Nuxt3 🍳  WordPress

<img src="https://e-s.tw/wp-content/uploads/2025/08/Company-Cover.jpg" />

ES 開發的 Nuxt3 x WordPress 版本，專門使用在客製化專案客戶。
如有在使用上遇到困難或者有改進建議的地方歡迎到 [Issues](https://github.com/esdesignstudio/es-nuxt3-template/issues) 提交問題，或者來信 [hi@e-s.tw](mailto:hi@e-s.tw)。
免費商用，請隨意下載。

## 環境
- Docker
- Node -- v23.6.0
- pnpm -- v10.13.1

## 開發安裝步驟
1. 安裝 Docker desktop
2. 到 .env 定基本環境，修改資料庫密碼
3. `docker-compose up -d`
4. `cd nuxt-app && pnpm install && pnpm dev`
5. 專案啟動\
   前端：`http://localhost:3000`\
   後台：`http://localhost:9000/wp-es-login`\
   後台初始使用者： `admin` 密碼： `test1234`
6. 修改管理者密碼

## 開發方式

### Nuxt Server Cache
``` javascript
// 取得快取資料
const { global, page, works } = usePageData().value
```

### WordPress 主題
``` php
├─ function.php 組裝所有資料
├─ /setting 主題設定檔
├─ /api
 　├─ /router API 路徑
 　└─ index.php 組裝 API

// function.php
// 這裡設定有快取的 Post type
$ALLOWED_POST_TYPES = array('page', 'works');
```
## 資料庫輸出
`sh shell/export_db.sh` 將 docker VM 的 DB 資料匯出至 `/db/wp.sql`


## 部署流程
請參考 [ES 2025 部署流程 ↗︎](https://www.notion.so/esdesign/2025-2303733e083480bdb75cffdbf1514654)