-- Script SQL para criar as tabelas do sistema de gestao de pedidos
-- Execute este script no MySQL para configurar o banco de dados

CREATE DATABASE IF NOT EXISTS hlp_controle;
USE hlp_controle;

-- Tabela de empresas (multiempresa)
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de usuarios para autenticacao
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    nome_usuario VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    empresa_id INT NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tamanho VARCHAR(20),
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    empresa_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de categorias de produtos
CREATE TABLE categorias_produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_categoria_empresa_nome (empresa_id, nome)
);

-- Tabela de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    data DATE NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    empresa_id INT NOT NULL,
    observacoes TEXT,
    status ENUM('Produção', 'Pronto', 'Entregue') DEFAULT 'Produção',
    pagamento ENUM('Pago', 'Pendente') DEFAULT 'Pendente',
    total DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de itens do pedido
CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    confirmado TINYINT(1) NOT NULL DEFAULT 0,
    confirmado_quantidade INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Inserir empresa padrao
INSERT INTO empresas (id, nome) VALUES (1, 'Empresa Padrão');

-- Inserir usuario admin padrao (senha: admin123)
INSERT INTO usuarios (username, nome_usuario, password, role, empresa_id, ativo)
VALUES ('admin', 'Administrador', '$2y$12$ofP0iCu1dmRsSr26HHHgD.sZtdrRVX7uk2YbLPCfud.Ow0QllqkSW', 'admin', 1, 1);
