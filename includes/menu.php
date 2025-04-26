<?php
require_once '../php/Session.php'; // Inclui a classe de Sessão
require_once '../config/config.php'; // Inclui a configuração do banco de dados

// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect(); // Conexão com o banco de dados

// Inicia a sessão e passa a conexão com o banco de dados
Session::start($conn);

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/loan-system/');
}

// Obtém o nome do usuário logado
$username = null;
if (Session::exists('user_id')) {
    $userId = Session::get('user_id');
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $username = $result['username'];
    }
}
?>
<aside>
    <h3 class="text-center text-white">Menu</h3>
    <p class="text-center text-white">
        <?php
        echo $username ? "Bem-vindo, " . htmlspecialchars($username) : "Usuário não identificado";
        ?>
    </p>
    <nav>
        <a href="<?= BASE_URL ?>system/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>system/listar_clientes.php"><i class="fas fa-users"></i> Clientes</a>       
        <a href="<?= BASE_URL ?>system/relatorio.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
        <a href="<?= BASE_URL ?>adm/user_list.php"><i class="fas fa-cogs"></i> Configurações</a>
        <a href="<?= BASE_URL ?>system/backup"><i class="fas fa-cogs"></i> Backup</a>
        <a href="<?= BASE_URL ?>system/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </nav>
</aside>
