<?php
// actions/pedido_crud.php - CRUD para pedidos
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_once '../config/db.php';
require_once '../config/categorias.php';
require_once '../config/pedidos_helpers.php';

ensure_session_started();
$empresa_id = current_empresa_id();

ensure_pedidos_categoria_varchar($pdo);
ensure_categorias_produto_table($pdo, $empresa_id);
ensure_itens_confirmado_column($pdo);
ensure_itens_confirmado_quantidade_column($pdo);

$action = $_POST['action'] ?? '';
$redirect_to = $_POST['redirect_to'] ?? '../pages/pedidos_central.php';

if (!is_string($redirect_to) || trim($redirect_to) === '' || preg_match('/^https?:\/\//i', $redirect_to)) {
    $redirect_to = '../pages/pedidos_central.php';
}

switch ($action) {
    case 'create':
        $cliente_nome = trim($_POST['cliente_nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $data = $_POST['data'] ?? '';
        $categoria = trim($_POST['categoria'] ?? '');
        $pagamento = $_POST['pagamento'] ?? 'Pendente';
        $observacoes = trim($_POST['observacoes'] ?? '');
        $produtos = $_POST['produtos'] ?? [];

        if ($cliente_nome === '' || $data === '' || empty($produtos)) {
            $_SESSION['message'] = 'Dados invalidos ou nenhum produto selecionado.';
            header('Location: ../pages/pedidos.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias_produto WHERE nome = ? AND empresa_id = ?");
        $stmt->execute([$categoria, $empresa_id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['message'] = 'Categoria invalida.';
            header('Location: ../pages/pedidos.php');
            exit;
        }

        foreach ($produtos as $produto) {
            $stmt = $pdo->prepare("SELECT categoria FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$produto['id'], $empresa_id]);
            $produto_categoria = $stmt->fetchColumn();

            if ($produto_categoria !== $categoria) {
                $_SESSION['message'] = 'Todos os produtos devem pertencer a categoria selecionada (' . $categoria . ').';
                header('Location: ../pages/pedidos.php');
                exit;
            }
        }

        $total = 0;
        foreach ($produtos as $produto) {
            $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$produto['id'], $empresa_id]);
            $preco = $stmt->fetchColumn();
            $total += $preco * $produto['quantidade'];
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_nome, telefone, data, categoria, empresa_id, observacoes, total, status, pagamento) VALUES (?, ?, ?, ?, ?, ?, ?, 'Produção', ?)");
            $stmt->execute([$cliente_nome, $telefone, $data, $categoria, $empresa_id, $observacoes, $total, $pagamento]);
            $pedido_id = $pdo->lastInsertId();

            foreach ($produtos as $produto) {
                $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$produto['id'], $empresa_id]);
                $preco = $stmt->fetchColumn();

                $stmt = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmt->execute([$pedido_id, $produto['id'], $produto['quantidade'], $preco]);
            }

            $pdo->commit();
            $_SESSION['message'] = 'Pedido criado com sucesso.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Erro ao criar pedido.';
        }
        break;

    case 'update_status':
        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $empresa_id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['message_error'] = 'Pedido não encontrado para sua empresa.';
            break;
        }

        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$status, $id, $empresa_id]);
            $_SESSION['message'] = 'Status atualizado com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Erro ao atualizar status.';
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $cliente_nome = trim($_POST['cliente_nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $data = $_POST['data'] ?? '';
        $categoria = trim($_POST['categoria'] ?? '');
        $pagamento = $_POST['pagamento'] ?? 'Pendente';
        $observacoes = trim($_POST['observacoes'] ?? '');
        $produtos = $_POST['produtos'] ?? [];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $empresa_id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['message_error'] = 'Pedido não encontrado para sua empresa.';
            header('Location: ../pages/pedidos_central.php?aba=listar');
            exit;
        }

        if ($cliente_nome === '' || $data === '' || empty($produtos)) {
            $_SESSION['message'] = 'Dados invalidos ou nenhum produto selecionado.';
            header('Location: ../pages/pedidos.php?id=' . $id);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias_produto WHERE nome = ? AND empresa_id = ?");
        $stmt->execute([$categoria, $empresa_id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['message'] = 'Categoria invalida.';
            header('Location: ../pages/pedidos.php?id=' . $id);
            exit;
        }

        foreach ($produtos as $produto) {
            $stmt = $pdo->prepare("SELECT categoria FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$produto['id'], $empresa_id]);
            $produto_categoria = $stmt->fetchColumn();

            if ($produto_categoria !== $categoria) {
                $_SESSION['message'] = 'Todos os produtos devem pertencer a categoria selecionada (' . $categoria . ').';
                header('Location: ../pages/pedidos.php?id=' . $id);
                exit;
            }
        }

        $total = 0;
        foreach ($produtos as $produto) {
            $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$produto['id'], $empresa_id]);
            $preco = $stmt->fetchColumn();
            $total += $preco * $produto['quantidade'];
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE pedidos SET cliente_nome = ?, telefone = ?, data = ?, categoria = ?, observacoes = ?, total = ?, pagamento = ? WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$cliente_nome, $telefone, $data, $categoria, $observacoes, $total, $pagamento, $id, $empresa_id]);

            $stmt = $pdo->prepare("DELETE FROM itens_pedido WHERE pedido_id = ?");
            $stmt->execute([$id]);

            foreach ($produtos as $produto) {
                $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = ? AND empresa_id = ?");
                $stmt->execute([$produto['id'], $empresa_id]);
                $preco = $stmt->fetchColumn();

                $stmt = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $produto['id'], $produto['quantidade'], $preco]);
            }

            $pdo->commit();
            $_SESSION['message'] = 'Pedido atualizado com sucesso.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Erro ao atualizar pedido.';
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $empresa_id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['message_error'] = 'Pedido não encontrado para sua empresa.';
            break;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM itens_pedido WHERE pedido_id = ?");
            $stmt->execute([$id]);

            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $empresa_id]);

            $pdo->commit();
            $_SESSION['message'] = 'Pedido excluido com sucesso.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Erro ao excluir pedido.';
        }
        break;
}

header('Location: ' . $redirect_to);
exit;
?>
