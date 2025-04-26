<?php
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

// Obtém o ID do empréstimo da URL
$id = $_GET['id'];

// Cria uma nova instância da classe Emprestimo
$emprestimo = new Emprestimo();

// Obtém as informações do empréstimo
$dadosEmprestimo = $emprestimo->obterEmprestimo($id);
$title = 'Detalhes do Empréstimo';
ob_start();
?>

    <main>
        <h2>Empréstimo ID: <?php echo $dadosEmprestimo['id']; ?></h2>
        <p><strong>Valor Total:</strong> <?php echo $dadosEmprestimo['valor_total']; ?></p>
        <p><strong>Taxa de Juros:</strong> <?php echo $dadosEmprestimo['taxa_juros']; ?>%</p>
        <p><strong>Quantidade de Parcelas:</strong> <?php echo $dadosEmprestimo['quantidade_parcelas']; ?></p>
        <p><strong>Data de Início:</strong> <?php echo $dadosEmprestimo['data_inicio']; ?></p>
        <a href="listar_emprestimos_cliente.php?cliente_id=<?php echo $dadosEmprestimo['cliente_id']; ?>">Voltar</a>
    </main>
    <?php
$content = ob_get_clean();
include '../includes/template.php';