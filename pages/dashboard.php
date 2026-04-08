<?php
// pages/dashboard.php - Dashboard principal
$page_title = 'Dashboard';
require_once 'header.php';
require_once '../config/db.php';

$empresa_id = current_empresa_id();
$hora = intval(date('G'));
$saudacao = 'Ola';
if ($hora >= 5 && $hora < 12) {
    $saudacao = 'Bom dia';
} elseif ($hora >= 12 && $hora < 18) {
    $saudacao = 'Boa tarde';
} else {
    $saudacao = 'Boa noite';
}
$usuario_nome = $_SESSION['nome_usuario'] ?? ($_SESSION['username'] ?? 'usuario');
$empresa_nome = $_SESSION['empresa_nome'] ?? 'Empresa Padrão';

// Consultas para os indicadores
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE empresa_id = ?");
$stmt->execute([$empresa_id]);
$total_pedidos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total) FROM pedidos WHERE status = 'Entregue' AND empresa_id = ?");
$stmt->execute([$empresa_id]);
$faturamento_total = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE status = 'Produção' AND empresa_id = ?");
$stmt->execute([$empresa_id]);
$pedidos_producao = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE status = 'Entregue' AND empresa_id = ?");
$stmt->execute([$empresa_id]);
$pedidos_entregues = $stmt->fetchColumn();
?>
            <h2>Dashboard</h2>
            <p><?php echo $saudacao . ' ' . htmlspecialchars($usuario_nome) . '!'; ?></p>
            <p>Empresa: <?php echo htmlspecialchars($empresa_nome); ?></p>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total de Pedidos</h3>
                    <p class="number"><?php echo $total_pedidos; ?></p>
                </div>
                <div class="card">
                    <h3>Faturamento Total</h3>
                    <p class="number">R$ <?php echo number_format($faturamento_total, 2, ',', '.'); ?></p>
                </div>
                <div class="card">
                    <h3>Em Produção</h3>
                    <p class="number"><?php echo $pedidos_producao; ?></p>
                </div>
                <div class="card">
                    <h3>Entregues</h3>
                    <p class="number"><?php echo $pedidos_entregues; ?></p>
                </div>
            </div>
<?php require_once 'footer.php'; ?>
