#!/usr/bin/env bash
# exit on error
set -o errexit

echo "--- Dang cai dat thu vien PHP (Composer) ---"
composer install --no-interaction --no-dev --optimize-autoloader

echo "--- Dang cai dat thu vien JS (NPM) ---"
npm install

echo "--- Dang bien dich giao dien (Vite) ---"
npm run build

echo "--- Dang lam sach va toi uu Cache ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Dang chay Database Migration ---"
# Bat buoc dung --force cho moi truong production
php artisan migrate --force

echo "--- Build thanh cong! ---"
