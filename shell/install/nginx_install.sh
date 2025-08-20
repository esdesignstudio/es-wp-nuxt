#!/bin/sh

# 安裝nginx
sudo apt-get install -y nginx
sudo ufw allow 'Nginx Full'
sudo systemctl restart nginx