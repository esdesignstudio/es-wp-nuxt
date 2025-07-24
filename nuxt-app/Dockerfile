FROM node:20-alpine

# 安装 PM2
RUN yarn global add pm2

WORKDIR /app

# 複製完整的建置結果
COPY .output ./.output
COPY ecosystem.config.js ./ecosystem.config.js

# 設定對外端口
EXPOSE 4001

# 用 PM2 啟動
CMD pm2-runtime start ecosystem.config.js --env prod