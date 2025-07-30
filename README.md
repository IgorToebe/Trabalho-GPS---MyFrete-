# MyFrete - Sistema de Frete e Entrega

Sistema web para conectar clientes que precisam transportar cargas com fretistas disponíveis.

## 🚀 Funcionalidades

- **Cadastro de Usuários**: Clientes e entregadores
- **Autenticação**: Login seguro com criptografia
- **Gestão de Fretes**: Criação, atualização e rastreamento de pedidos
- **Sistema de Avaliações**: Feedback entre clientes e entregadores
- **Interface Responsiva**: Funciona em desktop e mobile

## 🏗️ Tecnologias

- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Backend**: PHP 8.2 com PDO
- **Banco de Dados**: PostgreSQL
- **Containerização**: Docker & Docker Compose

## 📦 Instalação

### Pré-requisitos
- Docker
- Docker Compose

### Executar Localmente
```bash
# Clone o repositório
git clone https://github.com/IgorToebe/Trabalho-GPS---MyFrete-.git
cd Trabalho-GPS---MyFrete-

# Iniciar os containers
docker-compose up -d

# Acessar a aplicação
http://localhost:8080
```

## 🎯 Estrutura da Aplicação

```
├── api/                    # API REST em PHP
│   ├── controllers/        # Controladores da API
│   └── models/            # Modelos de dados
├── config/                # Configurações de banco e ambiente
├── pages/                 # Páginas HTML
├── scripts/               # JavaScript da aplicação
├── styles/                # CSS da aplicação
└── docker-compose.yaml    # Configuração Docker
```

## 🔗 Endpoints da API

- `POST /api/login_usuarios` - Cadastro de usuários
- `POST /api/login` - Autenticação
- `GET|POST|PUT|DELETE /api/fretes` - Gestão de fretes
- `GET|POST /api/frete_avaliacao` - Sistema de avaliações

## 🌐 Deploy

Para deploy em produção, configure as variáveis de ambiente:
- `DB_HOST` - Host do banco PostgreSQL
- `DB_PORT` - Porta do banco (padrão: 5432)
- `DB_NAME` - Nome do banco de dados
- `DB_USER` - Usuário do banco
- `DB_PASS` - Senha do banco

## 📄 Licença

Este projeto foi desenvolvido para fins acadêmicos.