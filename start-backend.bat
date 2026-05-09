@echo off
cd /d "%~dp0backend"
echo Iniciando FinanceFlow Backend...
php artisan serve --host=0.0.0.0 --port=8000
