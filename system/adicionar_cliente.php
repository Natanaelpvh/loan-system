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

// Cria uma nova instância da classe Cliente
$cliente = new Cliente();

// Verifica se o formulário foi enviado
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

    // Adiciona o cliente e obtém a mensagem de retorno
    $mensagem = $cliente->adicionarCliente($nome, $cpf, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $status);
}

$title = 'Adicionar Cliente';
ob_start();
?>

<header>
    <h2 class="mb-4">Adicionar Cliente</h2>
</header>

<?php if (isset($mensagem)): ?>
    <div class="alert alert-info" role="alert">
        <?php echo $mensagem; ?>
    </div>
<?php endif; ?>

<form method="POST" action="adicionar_cliente.php">
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" class="form-control" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group col-md-6">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" class="form-control" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" class="form-control" required>
        </div>
        <div class="form-group col-md-2">
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" class="form-control" required>
        </div>
        <div class="form-group col-md-4">
            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" class="form-control" required>
        </div>
    </section>
    <section class="form-row">
        <div class="form-group col-md-6">
            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" class="form-control" required>
        </div>
        <div class="form-group col-md-4">
            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" class="form-control" required>
        </div>
        <div class="form-group col-md-2">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" class="form-control" required>
        </div>
    </section>
    <section class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status" class="form-control" required>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
        </select>
    </section>
    <button type="submit" class="btn btn-success">Adicionar Cliente</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#cpf').mask('000.000.000-00');
        $('#telefone').mask('(00) 0 0000-0000');
        $('#cep').mask('00000-00');
    });
</script>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>