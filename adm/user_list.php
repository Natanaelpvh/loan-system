<?php
require_once '../config/config.php'; // Inclui a configuração da conexão com o banco de dados
require_once '../php/User.php'; // Inclui a classe User
require_once '../php/Session.php'; // Inclui a classe de Sessão

// Instancia a classe Config e cria a conexão
$config = new Config();
$conn = $config->connect();

// Passa a conexão para o Session::start()
Session::start($conn); // Agora passamos a conexão para a função start()

// Verifica se o usuário está logado
if (!Session::exists('user_id')) {
    header("Location: login.php");  // Redireciona para o login se não estiver logado
    exit();
}

// Verifica se o usuário tem o papel de admin
Session::requireRole('admin');

// Cria a instância da classe User
$user = new User(); // Não é necessário passar $conn aqui, pois a conexão é gerenciada internamente

// Obtém todos os usuários cadastrados
$users = $user->listUsers();

// Verifica se há uma mensagem na URL e exibe a mensagem
if (isset($_GET['mensagem']) && isset($_GET['alertClass'])) {
    $mensagem = urldecode($_GET['mensagem']);
    $alertClass = urldecode($_GET['alertClass']);
    echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
            $mensagem
            <button type='button' class='close' data-dismiss='alert' aria-label='Fechar'>
                <span aria-hidden='true'>&times;</span>
            </button>
          </div>";
}

$title = "Lista de Usuários";
ob_start();
?>

<div class="container mt-5">
    <h2>Usuários Cadastrados</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuário</th>
                <th>Email</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (is_array($users)) {
                foreach ($users as $user_data) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user_data['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user_data['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($user_data['email']) . "</td>";
                    echo "<td><a href='edit_user.php?id=" . $user_data['id'] . "' class='btn btn-warning'>Editar</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>" . $users . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>
