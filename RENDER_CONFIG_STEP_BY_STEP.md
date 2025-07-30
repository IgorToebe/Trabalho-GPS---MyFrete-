# 🚀 CONFIGURAÇÃO RENDER - INSTRUÇÕES ESPECÍFICAS

## ⚠️ PROBLEMA IDENTIFICADO

O Render não está carregando as variáveis de ambiente corretamente. Vamos resolver isso definitivamente.

## 📋 PASSO A PASSO PARA CONFIGURAR NO RENDER

### 1. Acessar o Painel do Render

- Vá para: https://dashboard.render.com
- Selecione seu serviço "MyFrete"

### 2. Configurar Environment Variables

No painel do serviço, vá em **Environment** e adicione EXATAMENTE estas variáveis:

```
Key: DB_HOST
Value: dpg-d1sjc3re5dus73b3pre0-a.virginia-postgres.render.com

Key: DB_PORT
Value: 5432

Key: DB_NAME
Value: sbt_bd

Key: DB_USER
Value: sebodetraca

Key: DB_PASS
Value: Ye4TSEiib3f5WWoUOJILs9gKKlclqu1g

Key: APP_ENV
Value: production
```

### 3. Verificar Configuração do Banco

No painel do PostgreSQL no Render, confirme:

- ✅ Database está **Available**
- ✅ Connection String está correto
- ✅ SSL está habilitado

### 4. Configurações do Serviço Web

#### Build Command:

```bash
# Deixe vazio ou use:
echo "Build completed"
```

#### Start Command:

```bash
bash render-start.sh
```

#### Environment:

- **Runtime**: `Docker`
- **Region**: `Ohio (US East)`

### 5. Deploy e Teste

1. **Fazer Deploy**: Clique em "Manual Deploy" → "Deploy latest commit"

2. **Aguardar Deploy**: Espere aparecer "Live" (pode levar 2-5 minutos)

3. **Testar URLs**:
   - `https://seu-app.onrender.com/test_render_connection_v2.php`
   - `https://seu-app.onrender.com/api/debug/env`
   - `https://seu-app.onrender.com/debug_api.html`

## 🔧 SISTEMA DE FALLBACK IMPLEMENTADO

### Prioridade de Carregamento:

1. **DATABASE_URL** (Render's internal format)
2. **Variáveis individuais** (DB_HOST, DB_PORT, etc.)
3. **Arquivo .env** (fallback local)
4. **Hardcoded values** (emergência)

### Como Verificar se Funcionou:

Acesse: `https://seu-app.onrender.com/test_render_connection_v2.php`

**✅ Resultado Esperado:**

```
=== MyFrete Database Connection Test ===
Environment loaded from: render_env_vars
Environment Variables:
DB_HOST: dpg-d1sjc3re5dus73b3pre0-a.virginia-postgres.render.com
DB_PORT: 5432
DB_NAME: sbt_bd
DB_USER: sebodetraca
DB_PASS: ***set***
✓ Database connection successful!
✓ PostgreSQL version: PostgreSQL 15.x
```

**❌ Se ainda não funcionar:**

```
Environment loaded from: render_fallback
DB_HOST: dpg-d1sjc3re5dus73b3pre0-a.virginia-postgres.render.com
(ainda assim deveria conectar)
```

## 🛠️ TROUBLESHOOTING

### Problema: "Environment Variables: not set"

**Causa**: Variáveis não configuradas no painel do Render
**Solução**:

1. Conferir se TODAS as 5 variáveis foram adicionadas
2. Fazer novo deploy após adicionar
3. Aguardar deploy completo

### Problema: "connection to server failed"

**Causa**: Database indisponível ou credenciais incorretas
**Solução**:

1. Verificar se PostgreSQL está "Available" no Render
2. Copiar credenciais exatas do painel do banco
3. Verificar se não há espaços extras nas variáveis

### Problema: ".env file not found"

**Causa**: Normal no Render (arquivo pode não ser enviado)
**Solução**: Sistema agora tem fallback hardcoded, deve funcionar mesmo assim

## 📱 MONITORAMENTO EM TEMPO REAL

### Logs do Deploy:

No painel do Render → **Logs** → acompanhe mensagens:

- `"Environment loaded from: render_env_vars"` ✅
- `"Database connection successful"` ✅
- `"Database connection failed"` ❌

### Interface de Debug:

- `https://seu-app.onrender.com/debug_api.html`
- Clique em "🌍 Variáveis de Ambiente"
- Deve mostrar todas as variáveis carregadas

## 🎯 PRÓXIMOS PASSOS

1. **Configure as variáveis no Render** (passo mais importante)
2. **Faça o deploy**
3. **Teste a URL de conexão**
4. **Se funcionou** → sistema está pronto!
5. **Se não funcionou** → verifique logs e troubleshooting

---

**💡 DICA:** O sistema agora tem 4 camadas de fallback, então DEVERIA funcionar mesmo que algo dê errado. Se ainda assim não conectar, o problema pode estar no próprio banco PostgreSQL no Render.
