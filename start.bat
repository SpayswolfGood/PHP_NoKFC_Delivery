@echo off
cd /d "%~dp0NoKFC_Delivery"
echo Открытие сайта в браузере: http://127.0.0.1:8000
start "" "http://127.0.0.1:8000"
php artisan serve
pause
