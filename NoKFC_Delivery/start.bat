@echo off
title NoKFC Delivery - Laravel Server
cd /d "%~dp0"

echo ========================================================
echo       Запуск NoKFC Delivery (Laravel 13 + PHP 8.4)
echo ========================================================
echo.
echo Открытие сайта в браузере: http://127.0.0.1:8000
start "" "http://127.0.0.1:8000"
echo.
echo Сервер запущен. Для остановки нажмите Ctrl + C.
echo.
php artisan serve
pause
