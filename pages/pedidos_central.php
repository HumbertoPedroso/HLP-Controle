<?php
// pages/pedidos_central.php - Central de pedidos
$page_title = 'Central de Pedidos';
require_once 'header.php';
require_once '../config/db.php';

// Verificar qual aba está ativa
$aba_ativa = $_GET['aba'] ?? 'listar';
?>
            <h2>Central de Pedidos</h2>
            
            <div class="pedidos-tabs">
                <a href="?aba=novo" class="tab-btn <?php echo $aba_ativa == 'novo' ? 'active' : ''; ?>">Novo Pedido</a>
                <a href="?aba=listar" class="tab-btn <?php echo $aba_ativa == 'listar' ? 'active' : ''; ?>">Todos os Pedidos</a>
                <a href="?aba=producao" class="tab-btn <?php echo $aba_ativa == 'producao' ? 'active' : ''; ?>">Produção</a>
                <a href="?aba=prontos" class="tab-btn <?php echo $aba_ativa == 'prontos' ? 'active' : ''; ?>">Prontos</a>
                <a href="?aba=entregues" class="tab-btn <?php echo $aba_ativa == 'entregues' ? 'active' : ''; ?>">Entregues</a>
                <a href="?aba=marcar_grupo" class="tab-btn <?php echo $aba_ativa == 'marcar_grupo' ? 'active' : ''; ?>">Marcar Grupo</a>
            </div>

            <div class="tab-content">
                <?php if ($aba_ativa == 'novo'): ?>
                    <?php require_once 'pedidos.php'; ?>
                    
                <?php elseif ($aba_ativa == 'listar'): ?>
                    <?php require_once 'lista_pedidos.php'; ?>
                    
                <?php elseif ($aba_ativa == 'producao'): ?>
                    <?php require_once 'producao.php'; ?>
                    
                <?php elseif ($aba_ativa == 'prontos'): ?>
                    <?php require_once 'prontos.php'; ?>
                    
                <?php elseif ($aba_ativa == 'entregues'): ?>
                    <?php require_once 'entregues.php'; ?>
                    
                <?php elseif ($aba_ativa == 'marcar_grupo'): ?>
                    <?php require_once 'marcar_grupo.php'; ?>
                    
                <?php endif; ?>
            </div>

<?php require_once 'footer.php'; ?>

