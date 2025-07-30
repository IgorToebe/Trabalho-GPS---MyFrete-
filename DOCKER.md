# Docker Setup - MyFrete

Este documento explica como executar o MyFrete usando Docker.

## Pré-requisitos

- Docker Desktop instalado
- Docker Compose instalado (já incluído no Docker Desktop)

## Estrutura dos Arquivos Docker

- `Dockerfile` - Configuração da imagem PHP com Apache
- `compose.yaml` - Configuração principal para produção
- `compose.debug.yaml` - Configuração para desenvolvimento/debug
- `docker-compose.override.yml` - Configurações locais de desenvolvimento
- `docker-entrypoint.sh` - Script de inicialização
- `.dockerignore` - Arquivos ignorados durante o build

## Como Executar

### Desenvolvimento (Recomendado)

```bash
# Usando compose.debug.yaml para desenvolvimento
docker-compose -f compose.debug.yaml up --build

# Ou simplesmente (usará compose.yaml + override automaticamente)
docker-compose up --build
```

### Produção

```bash
# Para produção
docker-compose -f compose.yaml up --build -d
```

## Serviços

### 1. PostgreSQL Database (`postgres`)

- **Porta:** 5432
- **Database:** myfrete
- **Usuário:** postgres
- **Senha:** postgres123
- **Volume:** `postgres_data` para persistência dos dados

### 2. Aplicação PHP (`myfrete-app`)

- **Porta:** 8080 (http://localhost:8080)
- **Servidor:** Apache com PHP 8.2
- **Documentos:** `/var/www/html`

## URLs de Acesso

- **Frontend:** http://localhost:8080
- **API:** http://localhost:8080/api/
- **Database:** localhost:5432

## Comandos Úteis

```bash
# Parar todos os serviços
docker-compose down

# Parar e remover volumes (apaga dados do banco)
docker-compose down -v

# Ver logs de um serviço específico
docker-compose logs myfrete-app
docker-compose logs postgres

# Executar comandos dentro do container
docker-compose exec myfrete-app bash
docker-compose exec postgres psql -U postgres -d myfrete

# Rebuild apenas um serviço
docker-compose build myfrete-app

# Executar em background
docker-compose up -d
```

## Configuração do Banco de Dados

O banco PostgreSQL é automaticamente configurado com:

- Criação do database `myfrete`
- Execução do script `database.sql` na inicialização
- Dados persistidos no volume `postgres_data`

## Variáveis de Ambiente

As seguintes variáveis são configuradas automaticamente:

### Aplicação PHP

- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_NAME=myfrete`
- `DB_USER=postgres`
- `DB_PASS=postgres123`
- `APP_ENV=development|production`

## Desenvolvimento

Durante o desenvolvimento:

- Os arquivos são montados como volume para hot reload
- PHP error reporting está habilitado
- Logs do Apache são acessíveis em `./logs/`

## Troubleshooting

### Problema: Aplicação não conecta com o banco

```bash
# Verificar se o PostgreSQL está rodando
docker-compose logs postgres

# Verificar conexão
docker-compose exec myfrete-app curl -f http://localhost/test_connection.php
```

### Problema: Permissões de arquivo

```bash
# Fixar permissões
docker-compose exec myfrete-app chown -R www-data:www-data /var/www/html
```

### Problema: Cache do Docker

```bash
# Limpar cache e rebuild
docker-compose down
docker system prune -f
docker-compose up --build --force-recreate
```

## Estrutura da Aplicação no Container

```
/var/www/html/
├── api/                 # API endpoints
├── pages/               # Páginas HTML
├── scripts/             # JavaScript
├── styles/              # CSS
├── images/              # Imagens
├── config/              # Configurações
├── .env                 # Variáveis de ambiente
├── .htaccess           # Configuração Apache
└── index.html          # Página principal
```
