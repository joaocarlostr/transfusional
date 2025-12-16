@echo off
REM ========================================
REM Script para Atualizar database.php
REM ========================================
echo.
echo ========================================
echo Atualizacao do arquivo database.php
echo ========================================
echo.
echo ATENCAO: Este script ira SOBRESCREVER o arquivo database.php existente
echo com a nova versao que inclui deteccao automatica de ambiente.
echo.
echo Pressione CTRL+C para cancelar ou
pause

echo.
echo Criando backup do arquivo atual...
copy "database.php" "database.php.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Backup criado com sucesso!
) else (
    echo [ERRO] Nao foi possivel criar backup
    pause
    exit /b 1
)

echo.
echo Copiando template para database.php...
copy /Y "database.php.template" "database.php" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Arquivo database.php atualizado com sucesso!
    echo.
    echo ========================================
    echo CONCLUIDO!
    echo ========================================
    echo.
    echo O arquivo database.php foi atualizado com:
    echo - Deteccao automatica de ambiente
    echo - Conexao para Desenvolvimento: 10.15.0.35
    echo - Conexao para Producao: 10.15.1.77
    echo.
    echo Um backup do arquivo anterior foi criado.
    echo.
) else (
    echo [ERRO] Nao foi possivel copiar o template
    pause
    exit /b 1
)

pause
