@echo off
cd /d "%~dp0NoKFC_Delivery"

start "" "http://127.0.0.1:8000"
start "Vite Dev Server" cmd /k "npm run dev"
php artisan serve
pause
