<?php
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

// Verifica se o ID foi passado na URL e se não está vazio
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do cliente não especificado.");
}

$id = $_GET['id'];
$clienteObj = new Cliente();
$cliente = $clienteObj->VerDetalheCliente($id);

// Se o cliente não for encontrado, exibe uma mensagem de erro
if (!$cliente) {
    die("Cliente não encontrado.");
}
$title = 'Detalhes do Cliente';
ob_start();
?>
<article class="container mt-5">
    <header class="mb-4">
        <h2 class="text-left text-primary">
            <i class="fas fa-user-circle"></i> Detalhes do Cliente
        </h2>
    </header>

    <section>
        <div class="row">
            <!-- Coluna de Dados Pessoais -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-id-card"></i> Dados Pessoais
                        </h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome']); ?></li>
                            <li class="list-group-item"><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></li>
                            <li class="list-group-item"><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['telefone']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Coluna de Endereço -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-map-marker-alt"></i> Endereço
                        </h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Endereço:</strong> <?php echo htmlspecialchars($cliente['rua']) . ", " . htmlspecialchars($cliente['numero']); ?></li>
                            <li class="list-group-item"><strong>Bairro:</strong> <?php echo htmlspecialchars($cliente['bairro']); ?></li>
                            <li class="list-group-item"><strong>Cidade:</strong> <?php echo htmlspecialchars($cliente['cidade']); ?></li>
                            <li class="list-group-item"><strong>Estado:</strong> <?php echo htmlspecialchars($cliente['estado']); ?></li>
                            <li class="list-group-item"><strong>CEP:</strong> <?php echo htmlspecialchars($cliente['cep']); ?></li>
                            <li class="list-group-item"><strong>Status:</strong> <?php echo htmlspecialchars($cliente['status']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center mt-4">
        <a href="listar_clientes.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </footer>
</article>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>
