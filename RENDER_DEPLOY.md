# Instruções para Deploy no Render

## Configuração das Variáveis de Ambiente no Render

### Passo 1: Configurar Environment Variables no Render
No painel do Render, vá em **Environment** e adicione:

```
DB_HOST=dpg-d1sjc3re5dus73b3pre0-a.virginia-postgres.render.com
DB_PORT=5432
DB_NAME=sbt_bd
DB_USER=sebodetraca
DB_PASS=Ye4TSEiib3f5WWoUOJILs9gKKlclqu1g
APP_ENV=production
```

### Passo 2: Verificar Start Command
No Render, configure o **Start Command** como:
```bash
bash render-start.sh
```

### Passo 3: Build Command
Configure o **Build Command** como:
```bash
# Render automatically runs composer install and npm install if needed
echo "Build completed"
```

## Teste das Funcionalidades

### 1. Após deploy, acesse:
- **Debug Interface**: `https://seu-app.onrender.com/debug_api.html`
- **API Test**: `https://seu-app.onrender.com/api/test`
- **Environment Variables**: `https://seu-app.onrender.com/api/debug/env`

### 2. Sequência de Testes Recomendada:
1. **🧩 Testar PHP Básico** - Verifica se PHP está funcionando
2. **🌍 Variáveis de Ambiente** - Verifica se as variables estão carregadas
3. **🗄️ Testar Conexão BD** - Verifica conexão com PostgreSQL
4. **🧪 Testar API Geral** - Verifica se o roteamento está funcionando
5. **📦 Testar Frete Update** - Verifica operações CRUD

## Troubleshooting

### Problema: Environment Variables "not set"
**Solução**: 
1. Verificar se as variáveis foram configuradas no painel do Render
2. Fazer redeploy após configurar as variáveis
3. Verificar se o arquivo .env está presente no repositório (fallback)

### Problema: Database Connection Failed
**Solução**:
1. Verificar se o PostgreSQL está ativo no Render
2. Confirmar se as credenciais estão corretas
3. Verificar se SSL está habilitado (sslmode=require)

### Problema: API retorna HTML em vez de JSON
**Solução**:
1. Verificar se .htaccess está configurado corretamente
2. Verificar se mod_rewrite está habilitado
3. Verificar logs de erro do PHP

## Estrutura de Logs

Os logs estão configurados para aparecer no console do Render. Procure por:
- `"Loaded environment variables from server"`
- `"Loading environment variables from .env file"`
- `"Database connection successful"`
- `"Database connection failed"`

## Sistema de Fallback

O sistema tem duplo fallback:
1. **Primeiro**: Tenta carregar variáveis do servidor (Render environment)
2. **Segundo**: Se não encontrar, carrega do arquivo .env
3. **Terceiro**: Se conexão falhar, usa mock database para demo

## Arquivos Importantes

- `/api/index.php` - Router principal da API
- `/config/database.php` - Configuração de banco com fallback
- `/debug_api.html` - Interface de debugging
- `/test_render_connection.php` - Teste específico para Render
- `.env` - Variáveis de ambiente (backup local)
- `render-start.sh` - Script de inicialização

## URLs de Teste Diretas

Após deploy, teste estas URLs:
- `GET /api/test` - Teste básico da API
- `GET /api/debug/env` - Debug das variáveis de ambiente
- `GET /test_render_connection.php` - Teste específico de conexão
- `GET /health_check.php` - Health check do sistema

## Monitoramento

Use o debug interface para monitorar:
- Status das variáveis de ambiente
- Conexão com banco de dados
- Funcionamento dos endpoints da API
- Logs de erro em tempo real
