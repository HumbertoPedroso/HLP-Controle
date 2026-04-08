# HLP Controle

Sistema web em PHP para controle de pedidos, produtos, producao e entrega, com autenticacao de usuarios e suporte a multiempresa.

## Tecnologias

- PHP puro
- MySQL
- HTML, CSS e JavaScript

## Principais funcionalidades

- Login com controle de sessao
- Dashboard com visao geral dos pedidos
- Cadastro e gerenciamento de produtos
- Cadastro de categorias de produtos
- Criacao de pedidos com multiplos itens
- Fluxo de status entre `Producao`, `Pronto` e `Entregue`
- Listagens e detalhes de pedidos
- Administracao de usuarios
- Separacao de dados por empresa

## Estrutura do projeto

```text
/
|-- actions/            # Processamento de login, logout, CRUD e acoes auxiliares
|-- assets/
|   |-- css/            # Estilos da aplicacao
|   |-- img/            # Icones e imagens
|   `-- js/             # Scripts do frontend
|-- config/             # Banco, autenticacao, multiempresa e helpers
|-- pages/              # Telas da aplicacao
|-- create_tables.sql   # Script de criacao do banco
|-- index.php           # Entrada principal
`-- install.php         # Instalacao automatica das tabelas
```

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache, Nginx ou ambiente local como XAMPP

## Configuracao

### 1. Banco de dados

Edite as credenciais em [config/db.php](/c:/Users/humbe/OneDrive/Documentos/HLP-controle/config/db.php).

Por padrao:

- Banco: `hlp_controle`
- Host: `localhost`
- Usuario: `root`
- Senha: definida no proprio arquivo

### 2. Instalacao

Voce pode instalar de duas formas:

1. Manualmente, executando o arquivo `create_tables.sql` no MySQL.
2. Automaticamente, acessando `install.php` no navegador.

### 3. Execucao local

Coloque o projeto no diretorio publico do seu servidor, por exemplo `htdocs`, e acesse:

```text
http://localhost/HLP-controle/
```

## Primeiro acesso

- Usuario: `admin`
- Senha: `admin123`

## Modulos disponiveis

- `Dashboard`: resumo dos pedidos e indicadores principais
- `Produtos`: cadastro, edicao e exclusao
- `Categorias`: organizacao dos produtos por categoria
- `Pedidos`: criacao e acompanhamento dos pedidos
- `Producao / Prontos / Entregues`: acompanhamento do fluxo de trabalho
- `Relacao de produtos`: visualizacao consolidada dos itens
- `Perfil`: atualizacao de dados do usuario
- `Administracao`: gestao de usuarios e empresa

## Seguranca e arquitetura

- Uso de `PDO` com consultas preparadas
- Senhas armazenadas com hash
- Controle de sessao no backend
- Restricao de acesso para areas autenticadas e administrativas
- Bootstrap de recursos multiempresa em [config/tenant.php](/c:/Users/humbe/OneDrive/Documentos/HLP-controle/config/tenant.php)

## Observacoes

- O projeto possui suporte a multiempresa com empresa padrao criada automaticamente.
- O arquivo `install.php` ajuda a preparar o banco em ambientes novos.
- Se houver erro de conexao, revise as credenciais em [config/db.php](/c:/Users/humbe/OneDrive/Documentos/HLP-controle/config/db.php).

## Licenca

Uso interno / privado, salvo definicao diferente pelo responsavel do projeto.
