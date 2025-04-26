<?php
// Inclui os arquivos necessários
require_once '../php/Router.php';
require_once '../php/Cliente.php';
require_once '../php/Session.php'; // Inclui a classe de sessão

// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect();  // Cria a conexão com o banco

// Inicia a sessão e passa a conexão com o banco de dados
Session::start($conn);

// Verifica se o usuário está logado
if (!Session::exists('user_id')) {
    header("Location: login.php");  // Redireciona para o login se a sessão não existir
    exit();
}

// Cria uma instância da classe Cliente
$cliente = new Cliente();
// Obtém os primeiros 10 clientes com parcelas vencidas
//$clientesVencidos = $cliente->listarClientesComParcelasVencidas(0, 10);
$total_clientes = $cliente->contarClientes();
$totalClientesAtrasados = $cliente->contarClientesComParcelasVencidas();

// Define o título da página
$title = 'Dashboard';

// Inicia a captura do conteúdo HTML com o buffer de saída
ob_start();
?>

<h1 class="mb-4">Relatório de Controle de Clientes e Finanças</h1>
<div class="row">
    <article class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <h2 class="text-xs font-weight-bold text-primary text-uppercase mb-1 text-size-large">
                    Clientes Cadastrados
                </h2>
                <p class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_clientes; ?></p>
            </div>
        </div>
    </article>
    <article class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <h2 class="text-xs font-weight-bold text-danger text-uppercase mb-1 text-size-large">
                    <a href="listar_clientes.php?nome=&email=&status_parcela=ATRASADO" class="text-danger">Parcelas em Atraso</a>
                </h2>
                <p class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalClientesAtrasados; ?></p>
            </div>
        </div>
    </article>
    <article class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <h2 class="text-xs font-weight-bold text-warning text-uppercase mb-1 text-size-large">Total a Receber
                </h2>
                <p class="h5 mb-0 font-weight-bold text-gray-800">R$ 15,000</p>
            </div>
        </div>
    </article>
    <article class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <h2 class="text-xs font-weight-bold text-danger text-uppercase mb-1 text-size-large">Total em Atraso
                </h2>
                <p class="h5 mb-0 font-weight-bold text-gray-800">R$ 8,000</p>
            </div>
        </div>
    </article>
</div>

<?php
// Finaliza a captura do conteúdo HTML e o armazena em uma variável
$content = ob_get_clean();

// Inclui o template da página com o conteúdo capturado
include '../includes/template.php';
?>