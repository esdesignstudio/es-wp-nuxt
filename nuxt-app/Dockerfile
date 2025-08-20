FROM node:20-alpine

# 安裝 PM2
RUN npm install -g pm2

WORKDIR /app

# 複製完整的建置結果
COPY .output ./.output
COPY ecosystem.config.js ./ecosystem.config.js

# 設定對外端口（使用環境變數或預設值）
ARG NUXT_PORT=4001
EXPOSE ${NUXT_PORT}

# 用 PM2 啟動
CMD pm2-runtime start ecosystem.config.js --env deploy