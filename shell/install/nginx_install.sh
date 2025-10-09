#!/bin/sh

# 安裝nginx
echo "> 開始安裝 Nginx..."
sudo apt-get install -y nginx
sudo ufw allow 'Nginx Full'
sudo systemctl restart nginx