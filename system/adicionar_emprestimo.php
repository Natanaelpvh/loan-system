<?php
require_once '../php/Emprestimo.php';
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

// Verifica se o ID do cliente foi passado como parâmetro na URL
if (!isset($_GET['cliente_id'])) {
    // Redireciona para a página de listagem de clientes caso não tenha um ID válido
    header('Location: listar_clientes.php');
    exit;
}

$cliente_id = $_GET['cliente_id']; // Obtém o ID do cliente da URL

// Cria uma nova instância da classe Cliente
$cliente = new Cliente();
// Obtém as informações do cliente
$dadosCliente = $cliente->obterCliente($cliente_id);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se todos os campos foram preenchidos corretamente
    if (isset($_POST['valor_total'], $_POST['taxa_juros'], $_POST['quantidade_parcelas'], $_POST['data_inicio'], $_POST['dia_vencimento'], $_POST['tipo_juros'], $_POST['periodo_juros'])) {
        $valor_total = $_POST['valor_total'];
        $taxa_juros = $_POST['taxa_juros'];
        $quantidade_parcelas = $_POST['quantidade_parcelas'];
        $data_inicio = $_POST['data_inicio'];
        $dia_vencimento = $_POST['dia_vencimento'];
        $tipo_juros = $_POST['tipo_juros'];
        $periodo_juros = $_POST['periodo_juros'];

        // Verifica se os valores são válidos
        if (is_numeric($valor_total) && is_numeric($taxa_juros) && is_numeric($quantidade_parcelas)) {
            // Cria uma nova instância da classe Emprestimo
            $emprestimo = new Emprestimo();

            // Adiciona o novo empréstimo e obtém o ID do novo empréstimo
            if ($emprestimo->adicionarEmprestimo($cliente_id, $valor_total, $taxa_juros, $quantidade_parcelas, $data_inicio, $dia_vencimento, $tipo_juros, $periodo_juros)) {
                // Redireciona para a página de listagem de empréstimos do cliente com mensagem de sucesso
                header('Location: listar_emprestimos_cliente.php?cliente_id=' . $cliente_id . '&message=Empréstimo adicionado com sucesso!');
                exit();
            } else {
                $erro = "Erro ao adicionar o empréstimo.";
            }
        } else {
            $erro = 'Por favor, preencha os campos corretamente (somente números são permitidos nos campos de valor, taxa de juros e quantidade de parcelas).';
        }
    } else {
        $erro = 'Todos os campos são obrigatórios. Por favor, preencha todos os campos.';
    }
}

$title = 'Adicionar Empréstimo';
ob_start();
?>

<main class="container mt-5">
    <h2 class="mb-4">Adicionar Novo Empréstimo para <?php echo $dadosCliente['nome']; ?></h2>
    <?php if (isset($erro)): ?>
        <?php
        echo '<div class="container">
            <div class="alert alert-danger alert-dismissible fade show">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                 ' . $erro . '
            </div>
        </div>';
        ?>        
    <?php endif; ?>
    <form method="POST" action="adicionar_emprestimo.php?cliente_id=<?php echo $cliente_id; ?>" class="row g-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <label for="valor_total" class="form-label"><strong>Valor Total:</strong></label>
                    <input type="text" class="form-control" id="valor_total" name="valor_total" required>
                </div>
                <div class="col-md-3">
                    <label for="quantidade_parcelas" class="form-label"><strong>Quantidade de Parcelas:</strong></label>
                    <input type="text" class="form-control" id="quantidade_parcelas" name="quantidade_parcelas"
                        required>
                </div>
                <div class="col-md-3">
                    <label for="taxa_juros" class="form-label"><strong>Taxa de Juros (%):</strong></label>
                    <input type="text" class="form-control" id="taxa_juros" name="taxa_juros" required>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <label for="data_inicio" class="form-label"><strong>Data de Cadastro:</strong></label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                </div>
                <div class="col-md-6">
                    <label for="dia_vencimento" class="form-label"><strong>Dia de Vencimento:</strong></label>
                    <input type="date" class="form-control" id="dia_vencimento" name="dia_vencimento" required>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <label for="tipo_juros" class="form-label"><strong>Tipo de Juros:</strong></label>
                    <select class="form-control" id="tipo_juros" name="tipo_juros" required>
                        <option value="simples">Simples</option>
                        <option value="compostos">Compostos</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="periodo_juros" class="form-label"><strong>Período da Taxa de Juros:</strong></label>
                    <select class="form-control" id="periodo_juros" name="periodo_juros" required>
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary">Adicionar Empréstimo</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='listar_emprestimos_cliente.php?cliente_id=<?php echo $cliente_id; ?>'">Cancelar</button>
        </div>
    </form>
</main>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>