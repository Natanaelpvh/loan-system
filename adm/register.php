<?php
require_once '../config/config.php';
require_once '../php/User.php';
require_once '../php/Session.php';

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

// Inicializa a variável de mensagem
$message = '';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtendo os dados do formulário
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificação das senhas
    if ($password !== $confirm_password) {
        $_SESSION['message'] = ["tipo" => "danger", "texto" => "As senhas não coincidem."];
        header("Location: register.php"); // Redireciona para exibir a mensagem
        exit;
    }

    // Criando a instância de User e registrando o usuário
    $user = new User($conn);
    if ($user->register($username, $email, $password)) {
        $_SESSION['message'] = ["tipo" => "success", "texto" => "Registro realizado com sucesso!"];
    } else {
        $_SESSION['message'] = ["tipo" => "danger", "texto" => "Erro ao registrar usuário."];
    }

    header("Location: register.php"); // Redireciona para evitar reenvio do formulário
    exit;
}

$title = 'Registro';
ob_start();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="text-center">Registro</h2>

                    <!-- Exibição da Mensagem -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message']['tipo']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']['texto']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php unset($_SESSION['message']); // Remove a mensagem após exibir ?>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Usuário:</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Senha:</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Senha:</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>