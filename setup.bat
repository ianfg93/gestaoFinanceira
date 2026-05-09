@echo off
echo ============================================
echo  FinanceFlow - Setup Inicial
echo ============================================
echo.
echo [1/3] Criando banco de dados...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS financeflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo ERRO: Nao foi possivel conectar ao MySQL.
    echo Certifique-se que o XAMPP esta rodando com MySQL ativo.
    pause
    exit /b 1
)
echo Banco criado com sucesso.
echo.
echo [2/3] Executando migrations e seed...
cd /d "%~dp0backend"
php artisan migrate --seed --force
echo.
echo [3/3] Setup concluido!
echo.
echo Para iniciar o sistema:
echo   1. Execute start-backend.bat  (API na porta 8000)
echo   2. Execute start-frontend.bat (App na porta 5173)
echo.
echo Acesse: http://localhost:5173
echo Login: ian@financeflow.app / password
echo.
pause
