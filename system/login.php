<?php 
require_once '../config/config.php';
require_once '../php/Session.php'; // Inclui a classe de sessão

// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect();

// Verifica se a conexão foi bem-sucedida
if (!$conn) {
    die("Erro de conexão com o banco de dados.");
}

// Inicia a sessão e passa a conexão com o banco de dados
Session::start($conn);

// Verifica se o usuário já está logado
if (Session::exists('user_id')) {
    header("Location: dashboard.php"); // Redireciona se já estiver logado
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta para verificar as credenciais
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Utiliza a classe Session para criar a sessão do usuário
        Session::set('user_id', $user['id']);
        Session::set('role', $user['role']);
        header("Location: dashboard.php"); // Redireciona após login
        exit();
    } else {
        error_log("Falha no login: usuário $username tentou acessar.");
        $error = "Nome de usuário ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Inclusão do Bootstrap 4.5 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h3 class="card-title text-center mb-4">Login</h3>
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
        </div>
    </div>

    <!-- Inclusão do Bootstrap 4.5 JS e dependências -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>