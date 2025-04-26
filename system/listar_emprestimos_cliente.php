<?php
require_once '../php/Cliente.php';
require_once '../php/Emprestimo.php';
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

// Instancia os objetos das classes Cliente e Emprestimo
$cliente = new Cliente();
$emprestimo = new Emprestimo();

// Obtém os dados do cliente e seus empréstimos
$dadosCliente = $cliente->obterCliente($cliente_id);
$emprestimos = $emprestimo->listarEmprestimosPorCliente($cliente_id);

$title = 'Empréstimos do Cliente';
ob_start();


?>

<header>
    <!-- Botão para voltar à lista de clientes -->
    <a data-toggle="tooltip" data-placement="top" title="Voltar" href="listar_clientes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    <h2>Empréstimos de <?php echo htmlspecialchars($dadosCliente['nome']); ?></h2>
    <hr>
</header>

<!-- Botão para adicionar um novo empréstimo ao cliente -->
<a data-toggle="tooltip" data-placement="top" title="Add Novo Empréstimo" href="adicionar_emprestimo.php?cliente_id=<?php echo htmlspecialchars($cliente_id); ?>"
    class="btn btn-success mb-3"><i class="fa fa-plus"></i> </a>

<section>
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    <!-- Tabela que exibe a lista de empréstimos do cliente -->
    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Valor</th>
                <th>Valor com Juros</th>
                <th>Data Cadastro</th>
                <th>Taxa (%)</th>
                <th>Parcelas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emprestimos as $emprestimo): ?>
                <tr>
                    <td><?php echo htmlspecialchars($emprestimo['id']); ?></td>
                    <!-- Exibe o valor total formatado -->
                    <td>R$ <?php echo number_format($emprestimo['valor_total'], 2, ',', '.'); ?></td>
                    <!-- Exibe o valor total com juros previamente calculado -->
                    <td>R$ <?php echo number_format($emprestimo['valor_total_com_juros'], 2, ',', '.'); ?></td>
                    <!-- Formata e exibe a data de início do empréstimo -->
                    <td><?php echo date('d/m/Y', strtotime($emprestimo['data_inicio'])); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['taxa_juros']); ?></td>
                    <td><?php echo htmlspecialchars($emprestimo['quantidade_parcelas']); ?></td>
                    <td>
                      
                        <!-- Link para editar o empréstimo -->
                        <a data-toggle="tooltip" data-placement="top" title="Editar Emprestimo" href="editar_emprestimo.php?id=<?php echo htmlspecialchars($emprestimo['id']); ?>&cliente_id=<?php echo htmlspecialchars($cliente_id); ?>"
                            class="btn btn-warning btn-sm"><i class="far fa-edit"></i> </a>
                              <!-- Link para visualizar as parcelas do empréstimo -->
                        <a data-toggle="tooltip" data-placement="top" title="listar Parcelas" href="listar_parcelas_emprestimo.php?emprestimo_id=<?php echo htmlspecialchars($emprestimo['id']); ?>&cliente_id=<?php echo htmlspecialchars($cliente_id); ?>"
                            class="btn btn-info btn-sm"><i class="fa fa-eye"></i> </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>