<?php
require_once '../php/Cliente.php';
require_once '../php/Session.php'; // Inclui a classe de sessão


// Instancia a classe Config e obtém a conexão
$config = new Config();
$conn = $config->connect(); // Conexão com o banco de dados

// Inicia a sessão e passa a conexão com o banco de dados
Session::start($conn);

// Verifica se o usuário está logado
if (!Session::exists('user_id')) {
    header("Location: login.php");  // Redireciona para o login se a sessão não existir
    exit();
}

// Cria uma nova instância da classe Cliente
$cliente = new Cliente();

// Verifica se o formulário de busca foi enviado
$nome = '';
$cpf = '';
$status_parcela = ''; // Corrigir a inicialização da variável
$page = 1;
$limit = 10; // Número de clientes por página
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $nome = isset($_GET['nome']) ? $_GET['nome'] : '';
    $cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';
    $status_parcela = isset($_GET['status_parcela']) ? $_GET['status_parcela'] : '';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
}

// Calcula o offset para a consulta SQL
$offset = ($page - 1) * $limit;

// Obtém a lista de clientes filtrados
$clientes = $cliente->listarClientesFiltrados($nome, $cpf, $status_parcela, $offset, $limit);

// Conta o total de clientes filtrados
$total_clientes = $cliente->contarClientesFiltrados($nome, $cpf, $status_parcela);

// Calcula o número total de páginas
$total_pages = ceil($total_clientes / $limit);

$title = 'Lista de Clientes';
ob_start();
?>

<main class="container mt-0">
    <header>
        <h2>Clientes</h2>
    </header>
    <section>
        <form method="GET" action="listar_clientes.php" class="form-inline mb-3">
            <div class="form-group mr-2">
                <label for="nome" class="mr-2">Nome:</label>
                <input type="text" id="nome" name="nome" class="form-control"
                    value="<?php echo htmlspecialchars($nome); ?>">
            </div>
            <div class="form-group mr-2">
                <label for="cpf" class="mr-2">CPF:</label>
                <input type="text" id="cpf" name="cpf" class="form-control"
                    value="<?php echo htmlspecialchars($cpf); ?>">
            </div>
            <div class="form-group mr-2">
                <label for="status_parcela" class="mr-2">Status da Parcela:</label>
                <select id="status_parcela" name="status_parcela" class="form-control">
                    <option value="">Todos</option>
                    <option value="PAGO" <?php echo $status_parcela === 'PAGO' ? 'selected' : ''; ?>>PAGO</option>
                    <option value="PENDENTE" <?php echo $status_parcela === 'PENDENTE' ? 'selected' : ''; ?>>PENDENTE
                    </option>
                    <option value="ATRASADO" <?php echo $status_parcela === 'ATRASADO' ? 'selected' : ''; ?>>ATRASADO
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
        </form>
        <a href="adicionar_cliente.php" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Adicionar Novo
            Cliente</a>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert <?php echo strpos($_GET['message'], 'sucesso') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show"
                role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Status</th>
                        <th class="text-center" scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($cliente['id']); ?></th>
                            <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($cliente['status'])); ?></td>
                            <td class="text-right">
                                <a title="Editar Informações do Cliente"
                                    href="editar_cliente.php?id=<?php echo htmlspecialchars($cliente['id']); ?>"
                                    class="btn btn-success btn-sm"><i class="far fa-edit"></i></a>
                                <a href="#" class="btn btn-warning btn-sm detalhes-cliente" title="Ver Detalhes do Cliente"
                                    data-id="<?php echo htmlspecialchars($cliente['id']); ?>"><i class="fa fa-eye"></i>
                                </a>
                                <a href="listar_emprestimos_cliente.php?cliente_id=<?php echo htmlspecialchars($cliente['id']); ?>"
                                    class="btn btn-info btn-sm" title="Listar Empréstimos"><i class="fas fa-list-ol"></i>
                                </a>
                                <a href="adicionar_emprestimo.php?cliente_id=<?php echo htmlspecialchars($cliente['id']); ?>"
                                    class="btn btn-primary btn-sm" title="Adicionar Novo Empréstimo"><i
                                        class="fa fa-plus"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&nome=<?php echo urlencode($nome); ?>&cpf=<?php echo urlencode($cpf); ?>&status_parcela=<?php echo urlencode($status_parcela); ?>">&laquo;
                            Anterior</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page)
                        echo 'active'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&nome=<?php echo urlencode($nome); ?>&cpf=<?php echo urlencode($cpf); ?>&status_parcela=<?php echo urlencode($status_parcela); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&nome=<?php echo urlencode($nome); ?>&cpf=<?php echo urlencode($cpf); ?>&status_parcela=<?php echo urlencode($status_parcela); ?>">Próximo
                            &raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
</main>

<!-- Modal -->
<div class="modal fade" id="detalhesClienteModal" tabindex="-1" role="dialog"
    aria-labelledby="detalhesClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesClienteModalLabel">Detalhes do Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Nome:</strong> <span id="detalhesNome"></span></p>
                <p><strong>CPF:</strong> <span id="detalhesCPF"></span></p>
                <p><strong>Email:</strong> <span id="detalhesEmail"></span></p>
                <p><strong>Telefone:</strong> <span id="detalhesTelefone"></span></p>
                <p><strong>Endereço:</strong> <span id="detalhesEndereco"></span></p>
                <p><strong>Status:</strong> <span id="detalhesStatus"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>