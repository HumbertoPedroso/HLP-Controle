<?php
// actions/produto_crud.php - CRUD para produtos
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_once '../config/db.php';
require_once '../config/categorias.php';

$empresa_id = current_empresa_id();
ensure_categorias_produto_table($pdo, $empresa_id);

$action = $_POST['action'] ?? '';
$redirect_to = $_POST['redirect_to'] ?? '../pages/produtos.php';

if (!is_string($redirect_to) || trim($redirect_to) === '' || preg_match('/^https?:\/\//i', $redirect_to)) {
    $redirect_to = '../pages/produtos.php';
}

switch ($action) {
    case 'create_category':
        $nome_categoria = trim($_POST['nome_categoria'] ?? '');

        if ($nome_categoria === '') {
            $_SESSION['message_error'] = 'Informe o nome da categoria.';
            break;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO categorias_produto (empresa_id, nome) VALUES (?, ?)");
            $stmt->execute([$empresa_id, $nome_categoria]);
            $_SESSION['message'] = 'Categoria criada com sucesso.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['message_error'] = 'Essa categoria já existe.';
            } else {
                $_SESSION['message_error'] = 'Erro ao criar categoria.';
            }
        }
        break;

    case 'delete_category':
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['message_error'] = 'Categoria inválida.';
            break;
        }

        try {
            $stmt = $pdo->prepare("SELECT nome FROM categorias_produto WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $empresa_id]);
            $categoria_nome = $stmt->fetchColumn();

            if (!$categoria_nome) {
                $_SESSION['message_error'] = 'Categoria não encontrada.';
                break;
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE categoria = ? AND status <> 'Entregue' AND empresa_id = ?");
            $stmt->execute([$categoria_nome, $empresa_id]);
            $pedidos_abertos = intval($stmt->fetchColumn());

            if ($pedidos_abertos > 0) {
                $_SESSION['message_error'] = 'Não é possível excluir: há pedidos dessa categoria que ainda não foram entregues.';
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM categorias_produto WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $empresa_id]);
            $_SESSION['message'] = 'Categoria excluída com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao excluir categoria.';
        }
        break;

    case 'create':
        $nome = trim($_POST['nome'] ?? '');
        $tamanho = trim($_POST['tamanho'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');

        if ($nome === '' || $preco <= 0) {
            $_SESSION['message_error'] = 'Dados inválidos.';
            break;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias_produto WHERE empresa_id = ? AND nome = ?");
        $stmt->execute([$empresa_id, $categoria]);
        if (intval($stmt->fetchColumn()) === 0) {
            $_SESSION['message_error'] = 'Crie uma categoria antes de cadastrar produtos.';
            break;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, tamanho, preco, categoria, empresa_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $tamanho ?: null, $preco, $categoria, $empresa_id]);
            $_SESSION['message'] = 'Produto criado com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao criar produto.';
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $tamanho = trim($_POST['tamanho'] ?? '');
        $preco = floatval($_POST['preco'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');

        if ($nome === '' || $preco <= 0) {
            $_SESSION['message_error'] = 'Dados inválidos.';
            break;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias_produto WHERE empresa_id = ? AND nome = ?");
        $stmt->execute([$empresa_id, $categoria]);
        if (intval($stmt->fetchColumn()) === 0) {
            $_SESSION['message_error'] = 'Categoria inválida para sua empresa.';
            break;
        }

        try {
            $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, tamanho = ?, preco = ?, categoria = ? WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$nome, $tamanho ?: null, $preco, $categoria, $id, $empresa_id]);
            $_SESSION['message'] = 'Produto atualizado com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao atualizar produto.';
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $empresa_id]);
            $_SESSION['message'] = 'Produto excluído com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao excluir produto.';
        }
        break;
}

header('Location: ' . $redirect_to);
exit;
?>
