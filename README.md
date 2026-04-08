# Sistema de GestÃ£o de Pedidos - HLP Controle

Sistema web completo para gestÃ£o de pedidos de confecÃ§Ã£o de uniformes e bordados.

## Tecnologias Utilizadas

- **PHP** (puro, sem frameworks)
- **MySQL**
- **HTML5**, **CSS3**, **JavaScript** (puro)

## Estrutura do Projeto

```
/ (raiz)
â”œâ”€â”€ config/
â”‚   â””â”€â”€ db.php          # ConexÃ£o com banco de dados
â”œâ”€â”€ pages/
â”‚   â”œâ”€â”€ header.php      # CabeÃ§alho e menu
â”‚   â”œâ”€â”€ footer.php      # RodapÃ©
â”‚   â”œâ”€â”€ login.php       # PÃ¡gina de login
â”‚   â”œâ”€â”€ dashboard.php   # Dashboard principal
â”‚   â”œâ”€â”€ produtos.php    # CRUD de produtos
â”‚   â”œâ”€â”€ pedidos.php     # Cadastro de pedidos
â”‚   â”œâ”€â”€ lista_pedidos.php # Listagem com filtros
â”‚   â”œâ”€â”€ detalhes_pedido.php # Detalhes do pedido
â”‚   â”œâ”€â”€ producao.php    # Pedidos em produÃ§Ã£o
â”‚   â””â”€â”€ entregues.php   # Pedidos entregues
â”œâ”€â”€ actions/
â”‚   â”œâ”€â”€ login.php       # Processa login
â”‚   â”œâ”€â”€ logout.php      # Faz logout
â”‚   â”œâ”€â”€ produto_crud.php # CRUD produtos
â”‚   â””â”€â”€ pedido_crud.php # CRUD pedidos
â”œâ”€â”€ assets/
â”‚   â”œâ”€â”€ css/
â”‚   â”‚   â””â”€â”€ style.css   # Estilos CSS
â”‚   â””â”€â”€ js/
â”‚       â””â”€â”€ scripts.js  # Scripts JavaScript
â”œâ”€â”€ index.php           # PÃ¡gina inicial
â””â”€â”€ create_tables.sql   # Script SQL para criar banco
```

## InstalaÃ§Ã£o e ConfiguraÃ§Ã£o

### 1. PrÃ©-requisitos

- Servidor web (Apache/Nginx)
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Recomendado: XAMPP (para desenvolvimento local)

### 2. ConfiguraÃ§Ã£o do Banco de Dados

1. Crie um banco de dados MySQL chamado `hlp_controle`
2. Execute o script `create_tables.sql` no phpMyAdmin ou linha de comando
3. Verifique as credenciais em `config/db.php` (padrÃ£o: root, sem senha)

### 3. ConfiguraÃ§Ã£o do Servidor

1. Coloque os arquivos na pasta `htdocs` (XAMPP) ou equivalente
2. Acesse via navegador: `http://localhost/HLP-controle/`

### 4. Primeiro Acesso

- **Usuário:** admin
- **Senha:** admin123

## Funcionalidades

### ðŸ” AutenticaÃ§Ã£o

- Login seguro com hash de senha
- SessÃµes PHP
- Bloqueio de acesso não autorizado

### ðŸ“Š Dashboard

- Totais de pedidos
- Faturamento total
- Pedidos em produÃ§Ã£o/entregues
- Cards visuais

### ðŸ‘• Produtos

- CRUD completo (Criar, Listar, Editar, Excluir)
- ValidaÃ§Ã£o de dados
- Categorias

### ðŸ“¦ Pedidos

- Cadastro completo com mÃºltiplos produtos
- CÃ¡lculo automÃ¡tico de total
- SeleÃ§Ã£o dinÃ¢mica de produtos
- ValidaÃ§Ãµes

### ðŸ”„ Controle de Status

- Produção â†’ Pronto â†’ Entregue
- PÃ¡ginas separadas por status
- Cores visuais

### ðŸ“‹ Listagem e Filtros

- Busca por nome
- Filtros por status, categoria, data
- Ações rÃ¡pidas

### ðŸ§¾ Detalhes do Pedido

- Lista completa de produtos
- Totais calculados

## SeguranÃ§a

- Prepared statements para prevenir SQL Injection
- Senhas hashadas com password_hash()
- ValidaÃ§Ã£o de entrada
- SessÃµes seguras

## Interface

- Design moderno e responsivo
- UX profissional
- Feedback visual (mensagens)
- Mobile-friendly

## Desenvolvimento Futuro

O sistema está preparado para:

- MÃºltiplos usuários
- Controle por empresa
- ExpansÃ£o para SaaS
- RelatÃ³rios avanÃ§ados
- IntegraÃ§Ã£o com APIs

## Suporte

Para dÃºvidas ou problemas, verifique:

1. Logs de erro do PHP
2. ConexÃ£o com banco de dados
3. PermissÃµes de arquivos
4. ConfiguraÃ§Ãµes do servidor web

---

**Desenvolvido para gestÃ£o profissional de pedidos de confecÃ§Ã£o.**

