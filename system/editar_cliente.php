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

// Verifica se o usuário tem a role necessária
Session::requireRole('admin');

// Obtém o ID do cliente da URL
$id = $_GET['id'];

// Cria uma nova instância da classe Cliente
$cliente = new Cliente();

// Obtém as informações do cliente
$dadosCliente = $cliente->obterCliente($id);

// Verifica se o formulário foi enviado para atualizar os dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $status = $_POST['status'];

    // Atualiza as informações do cliente e redireciona com a mensagem de retorno
    if ($cliente->atualizarCliente($id, $nome, $cpf, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $status)) {
        $mensagem = 'Cliente atualizado com sucesso!';
        $alertClass = 'alert-success';
    } else {
        $mensagem = 'Erro ao atualizar cliente.';
        $alertClass = 'alert-danger';
    }
    header("Location: ../adm/user_list.php?mensagem=" . urlencode($mensagem) . "&alertClass=" . urlencode($alertClass));
    exit();
}

$title = 'Editar Cliente';
ob_start();
?>

<header>
    <h2 class="mb-4">Editar Cliente</h2>
</header>

<?php if (isset($mensagem)): ?>
    <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensagem; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<form method="POST" action="editar_cliente.php?id=<?php echo $id; ?>">
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" class="form-control" value="<?php echo $dadosCliente['nome']; ?>" required>
        </div>
        <div class="form-group col-md-6">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" class="form-control" value="<?php echo $dadosCliente['cpf']; ?>" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo $dadosCliente['email']; ?>" required>
        </div>
        <div class="form-group col-md-6">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" class="form-control" value="<?php echo $dadosCliente['telefone']; ?>" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" class="form-control" value="<?php echo $dadosCliente['rua']; ?>" required>
        </div>
        <div class="form-group col-md-2">
            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" class="form-control" value="<?php echo $dadosCliente['numero']; ?>" required>
        </div>
        <div class="form-group col-md-4">
            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" class="form-control" value="<?php echo $dadosCliente['bairro']; ?>" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" class="form-control" value="<?php echo $dadosCliente['cidade']; ?>" required>
        </div>
        <div class="form-group col-md-4">
            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" class="form-control" value="<?php echo $dadosCliente['estado']; ?>" required>
        </div>
        <div class="form-group col-md-2">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" class="form-control" value="<?php echo $dadosCliente['cep']; ?>" required>
        </div>
    </section>
    <section class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status" class="form-control" required>
            <option value="Ativo" <?php echo $dadosCliente['status'] == 'Ativo' ? 'selected' : ''; ?>>Ativo</option>
            <option value="Inativo" <?php echo $dadosCliente['status'] == 'Inativo' ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </section>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <button type="submit" class="btn btn-success">Atualizar Cliente</button>
            </div>
            <div class="col-md-6">
                <a data-toggle="tooltip" data-placement="top" title="Voltar" href="listar_clientes.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function () {
        $('#cpf').mask('000.000.000-00');
        $('#telefone').mask('(00) 0 0000-0000');
        $('#cep').mask('00000-000');
    });
</script>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>