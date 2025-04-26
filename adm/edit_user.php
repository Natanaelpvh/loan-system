<?php
require_once '../config/config.php'; // Inclui a conexão com o banco de dados
require_once '../php/User.php'; // Inclui a classe User

// Obtém a conexão com o banco de dados
$config = new Config();
$conn = $config->connect(); 

// Cria a instância da classe User
$user = new User($conn);

// Verifica se o id foi passado na URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Obtém os dados do usuário a ser editado
    $sql = "SELECT username, email, role FROM users WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        echo "Usuário não encontrado!";
        exit();
    }
} else {
    echo "ID do usuário não fornecido.";
    exit();
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Novo campo para o role

    // Verifica se as senhas coincidem
    if ($password !== $confirm_password) {
        echo "As senhas não coincidem.";
        exit;
    }

    // Se a senha foi informada, atualiza com a nova senha, caso contrário, mantém a senha atual
    if (!empty($password)) {
        $update_success = $user->update($user_id, $username, $email, $role, $password);
    } else {
        $update_success = $user->update($user_id, $username, $email, $role, null); // Não atualiza a senha
    }

    if ($update_success) {
        echo "Usuário atualizado com sucesso!";
        header("Location: user_list.php"); // Redireciona para a lista de usuários após atualização
        exit();
    } else {
        echo "Erro ao atualizar usuário.";
    }
}

$title = "Editar Usuário";
ob_start();
?>

<div class="container mt-5">
    <h2>Editar Usuário</h2>
    <form method="POST">
        <div class="form-group">
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Nova Senha:</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar Nova Senha:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
        </div>
        <div class="form-group">
            <label for="role">Papel:</label>
            <select id="role" name="role" class="form-control" required>
                <option value="user" <?php echo ($user_data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Atualizar</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>