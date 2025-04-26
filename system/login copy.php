<?php
require_once '../config/config.php';
require_once '../php/Session.php'; // Inclui a classe de sessão

// Inicia a sessão
Session::start();

// Verifica se o usuário já está logado
if (Session::exists('user_id')) {
    header("Location: dashboard.php"); // Redireciona se já estiver logado
    exit();
}

// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect(); // Conexão com o banco de dados

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
        $error = "Nome de usuário ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form method="POST">
        <label for="username">Nome de usuário:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>