<?php
require_once '../php/Session.php'; // Inclui a classe de Sessão


// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect(); // Conexão com o banco de dados

// Inicia a sessão e passa a conexão com o banco de dados
Session::start($conn);

// Verifica se o usuário está logado
if (!Session::exists('user_id')) {
    header("Location: login.php");  // Redireciona para o login se a sessão não existir
    exit();
}

$title = "Dashboard";
ob_start();
?>

<div class="container mt-5">
    <h1 class="text-center">Dashboard</h1>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Clientes</div>
                <div class="card-body">
                    <h5 class="card-title">150</h5>
                    <p class="card-text">Total de clientes cadastrados.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Empréstimos</div>
                <div class="card-body">
                    <h5 class="card-title">75</h5>
                    <p class="card-text">Empréstimos ativos no sistema.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Pagamentos Pendentes</div>
                <div class="card-body">
                    <h5 class="card-title">20</h5>
                    <p class="card-text">Pagamentos pendentes de clientes.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Relatório de Atividades</div>
                <div class="card-body">
                    <p class="card-text">Últimas atividades realizadas no sistema.</p>
                    <ul>
                        <li>Cliente João Silva atualizou seus dados.</li>
                        <li>Empréstimo aprovado para Maria Oliveira.</li>
                        <li>Pagamento recebido de Carlos Santos.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Notificações</div>
                <div class="card-body">
                    <p class="card-text">Notificações recentes do sistema.</p>
                    <ul>
                        <li>Backup realizado com sucesso.</li>
                        <li>Nova atualização disponível.</li>
                        <li>Usuário admin alterou configurações.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>