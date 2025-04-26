<?php
require_once '../php/Emprestimo.php';
require_once '../php/Parcela.php';
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

// Obtém o ID do empréstimo da URL
$id = $_GET['id'];

// Cria uma nova instância da classe Emprestimo
$emprestimo = new Emprestimo();

// Obtém as informações do empréstimo
$dadosEmprestimo = $emprestimo->obterEmprestimo($id);

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Apaga o empréstimo e suas parcelas
        if ($emprestimo->apagarEmprestimo($id)) {
            // Redireciona para a página de listagem de empréstimos do cliente
            header('Location: listar_emprestimos_cliente.php?cliente_id=' . $dadosEmprestimo['cliente_id']);
            exit();
        } else {
            $erro = "Erro ao apagar o empréstimo.";
        }
    } else {
        // Garantir que os dados sejam tratados corretamente
        $valor_total = round((float) $_POST['valor_total'], 2);
        
        // Converte taxa de juros de string formatada para float
        $taxa_juros = str_replace(',', '.', $_POST['taxa_juros']);
        $taxa_juros = round((float) $taxa_juros, 2);
        
        $quantidade_parcelas = (int) $_POST['quantidade_parcelas'];
        $data_inicio = $_POST['data_inicio'];
        $dia_vencimento = $_POST['dia_vencimento'];
        $tipo_juros = $_POST['tipo_juros'];
        $frequencia_juros = $_POST['frequencia_juros'];

        // Verifica se houve alteração nos valores
        if ($valor_total != $dadosEmprestimo['valor_total'] || 
            $taxa_juros != $dadosEmprestimo['taxa_juros'] || 
            $quantidade_parcelas != $dadosEmprestimo['quantidade_parcelas'] || 
            $data_inicio != $dadosEmprestimo['data_inicio'] ||
            $dia_vencimento != $dadosEmprestimo['dia_vencimento'] ||
            $tipo_juros != $dadosEmprestimo['tipo_juros'] ||
            $frequencia_juros != $dadosEmprestimo['frequencia_juros']) {
            
            // Atualiza as informações do empréstimo
            if ($emprestimo->atualizarEmprestimo($id, $valor_total, $taxa_juros, $quantidade_parcelas, $data_inicio, $dia_vencimento, $tipo_juros, $frequencia_juros)) {
                // Redireciona para a página de listagem de empréstimos do cliente com mensagem de sucesso
                header('Location: listar_emprestimos_cliente.php?cliente_id=' . $dadosEmprestimo['cliente_id'] . '&message=Empréstimo atualizado com sucesso!');
                exit();
            } else {
                $erro = "Erro ao atualizar o empréstimo.";
            }
        } else {
            // Redireciona para a página de listagem de empréstimos do cliente se não houver alteração
            header('Location: listar_emprestimos_cliente.php?cliente_id=' . $dadosEmprestimo['cliente_id']);
            exit();
        }
    }
}

// Formatação de exibição final
$taxa_juros_formatada = str_replace('.', ',', number_format((float)$dadosEmprestimo['taxa_juros'], 2, '.', ''));

$title = 'Editar Empréstimo';
ob_start();
?>

<div class="container">
    <div class="row border-bottom">
        <div class="col-md-12 mb-3">
            <form method="POST" action="editar_emprestimo.php?id=<?php echo $id; ?>"
                onsubmit="return confirmarApagar();" class="mt-3">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="btn btn-danger"> <i class="fa fa-trash"></i> Deletar</button>
            </form>
        </div>
    </div>
    <h2 class="mb-4">Editar Empréstimo</h2>
</div>

<?php if (isset($erro)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $erro; ?>
    </div>
<?php endif; ?>

<form method="POST" action="editar_emprestimo.php?id=<?php echo $id; ?>" class="row g-3">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <label for="valor_total" class="form-label"><strong>Valor Total:</strong></label>
                <input type="text" class="form-control" id="valor_total" name="valor_total" value="<?php echo $dadosEmprestimo['valor_total']; ?>" required>
            </div>
            <div class="col-md-3">
                <label for="quantidade_parcelas" class="form-label"><strong>Quantidade de Parcelas:</strong></label>
                <input type="text" class="form-control" id="quantidade_parcelas" name="quantidade_parcelas" value="<?php echo $dadosEmprestimo['quantidade_parcelas']; ?>" required>
            </div>
            <div class="col-md-3">
                <label for="taxa_juros" class="form-label"><strong>Taxa de Juros (%):</strong></label>
                <input type="text" class="form-control" id="taxa_juros" name="taxa_juros" value="<?php echo $taxa_juros_formatada; ?>" required>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <label for="data_inicio" class="form-label"><strong>Data de Início:</strong></label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $dadosEmprestimo['data_inicio']; ?>" required>
            </div>
            <div class="col-md-6">
                <label for="dia_vencimento" class="form-label"><strong>Dia de Vencimento:</strong></label>
                <input type="date" class="form-control" id="dia_vencimento" name="dia_vencimento" value="<?php echo $dadosEmprestimo['dia_vencimento']; ?>" required>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <label for="tipo_juros" class="form-label"><strong>Tipo de Juros:</strong></label>
                <select class="form-control" id="tipo_juros" name="tipo_juros" required>
                    <option value="simples" <?php echo $dadosEmprestimo['tipo_juros'] == 'simples' ? 'selected' : ''; ?>>Simples</option>
                    <option value="compostos" <?php echo $dadosEmprestimo['tipo_juros'] == 'compostos' ? 'selected' : ''; ?>>Compostos</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="frequencia_juros" class="form-label"><strong>Frequência dos Juros:</strong></label>
                <select class="form-control" id="frequencia_juros" name="frequencia_juros" required>
                    <option value="mensal" <?php echo $dadosEmprestimo['frequencia_juros'] == 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                    <option value="anual" <?php echo $dadosEmprestimo['frequencia_juros'] == 'anual' ? 'selected' : ''; ?>>Anual</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-primary">Atualizar Empréstimo</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='listar_emprestimos_cliente.php?cliente_id=<?php echo $dadosEmprestimo['cliente_id']; ?>'">Cancelar</button>
    </div>
</form>

<script>
    function confirmarApagar() {
        return confirm('Tem certeza de que deseja apagar este empréstimo? Esta ação não pode ser desfeita.');
    }
</script>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>