@echo off
echo =================================
echo    MyFrete - Docker Setup
echo =================================

echo Verificando se o Docker esta instalado...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Docker nao encontrado. Instale o Docker Desktop primeiro.
    echo Download: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo Docker encontrado!
echo.

echo Escolha uma opcao:
echo 1) Executar em modo desenvolvimento (recomendado)
echo 2) Executar em modo producao
echo 3) Parar todos os servicos
echo 4) Ver logs
echo 5) Limpar e resetar tudo
echo.

set /p choice="Digite sua escolha (1-5): "

if "%choice%"=="1" (
    echo.
    echo Iniciando em modo desenvolvimento...
    echo Acesse: http://localhost:8080
    echo API: http://localhost:8080/api/
    echo Teste da API: http://localhost:8080/test_api.html
    echo.
    docker-compose -f compose.debug.yaml up --build
) else if "%choice%"=="2" (
    echo.
    echo Iniciando em modo producao...
    echo Acesse: http://localhost:8080
    echo Teste da API: http://localhost:8080/test_api.html
    echo.
    docker-compose -f compose.yaml up --build -d
    echo Servicos iniciados em background.
    echo Use "docker-compose logs" para ver os logs.
) else if "%choice%"=="3" (
    echo.
    echo Parando todos os servicos...
    docker-compose down
    echo Servicos parados.
) else if "%choice%"=="4" (
    echo.
    echo Logs dos servicos:
    docker-compose logs --tail=50
) else if "%choice%"=="5" (
    echo.
    echo ATENCAO: Isso ira apagar todos os dados do banco!
    set /p confirm="Tem certeza? (s/N): "
    if /i "%confirm%"=="s" (
        echo Limpando tudo...
        docker-compose down -v
        docker system prune -f
        echo Limpeza concluida.
    ) else (
        echo Operacao cancelada.
    )
) else (
    echo Opcao invalida.
)

echo.
pause
