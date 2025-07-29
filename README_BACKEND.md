# MyFrete - Backend PHP

Este é o backend PHP para o aplicativo MyFrete, desenvolvido como trabalho de faculdade.

## Tecnologias Utilizadas

- **PHP puro** (sem frameworks)
- **PostgreSQL** como banco de dados
- **PDO** para interação com o banco de dados
- **API RESTful** com respostas em JSON

## Estrutura do Projeto

```
/
├── api/                    # API endpoints
│   ├── controllers/        # Controladores da API
│   ├── models/            # Modelos de dados
│   └── index.php          # Router principal da API
├── config/                 # Configurações
│   └── database.php       # Configuração do banco de dados
├── pages/                  # Páginas HTML do frontend
├── scripts/                # JavaScript do frontend
├── styles/                 # CSS do frontend
├── .env                    # Variáveis de ambiente
├── database.sql            # Script de criação das tabelas
└── test_connection.php     # Teste de conexão com o banco
```

## Configuração do Banco de Dados

1. Execute o script `database.sql` no PostgreSQL para criar as tabelas
2. Configure as credenciais no arquivo `.env`

## Estrutura do Banco de Dados

### Tabela: `login_usuarios`
- `id_usu` (INT, PK, auto-increment)
- `nomecompleto` (VARCHAR(100))
- `email` (VARCHAR(100), UNIQUE)
- `telefone` (VARCHAR(100))
- `senha` (VARCHAR(255) - hash)
- `ehentregador` (BOOLEAN)

### Tabela: `frete`
- `id_frete` (INT, PK, auto-increment)
- `id_cliente` (INT, FK)
- `id_fretista` (INT, FK, nullable)
- `data` (DATE)
- `hora` (TIME)
- `end_origem` (VARCHAR(100))
- `end_destino` (VARCHAR(100))
- `status` (VARCHAR(100))

### Tabela: `frete_avaliacao`
- `id_avaliacao` (INT, PK, auto-increment)
- `id_frete` (INT, FK)
- `nota` (INT, 1-5)
- `comentario` (VARCHAR(255))

## Endpoints da API

### Usuários
- `POST /api/login_usuarios` - Criar usuário
- `POST /api/login` - Autenticar usuário
- `GET /api/login_usuarios/{id}` - Obter usuário por ID
- `GET /api/login_usuarios/entregador` - Listar entregadores

### Fretes
- `POST /api/frete` - Criar frete
- `GET /api/frete` - Listar fretes (com filtros opcionais)
- `GET /api/frete/{id}` - Obter frete por ID
- `PUT /api/frete/{id}` - Atualizar frete
- `DELETE /api/frete/{id}` - Deletar frete

### Avaliações
- `POST /api/frete_avaliacao` - Criar avaliação
- `GET /api/frete_avaliacao` - Listar todas avaliações
- `GET /api/frete_avaliacao/{id_frete}` - Listar avaliações de um frete

## Como Usar

1. Configure um servidor web (Apache/Nginx) com PHP
2. Coloque os arquivos na pasta do servidor
3. Configure o banco PostgreSQL e execute o script `database.sql`
4. Ajuste as credenciais no arquivo `.env`
5. Acesse as páginas HTML através do navegador

## Recursos de Segurança

- Senhas são armazenadas como hash usando `password_hash()`
- Queries preparadas (PDO) para prevenir SQL Injection
- Validação de dados de entrada
- CORS configurado para desenvolvimento

## Teste de Conexão

Execute `php test_connection.php` para verificar se a conexão com o banco está funcionando.