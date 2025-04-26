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

// Obtém o ID do cliente da URL
$cliente_id = $_GET['cliente_id'];

// Cria uma nova instância da classe Cliente
$cliente = new Cliente();

// Cria uma nova instância da classe Emprestimo
$emprestimo = new Emprestimo();

// Obtém as informações do cliente
$dadosCliente = $cliente->obterCliente($cliente_id);

// Obtém a lista de empréstimos do cliente
$emprestimos = $emprestimo->listarEmprestimosPorCliente($cliente_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empréstimos do Cliente - Sistema de Empréstimos</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Link para um arquivo CSS, se necessário -->
</head>
<body>
    <header>
        <h1>Empréstimos do Cliente</h1>
        <?php include '../includes/menu.php'; ?>
    </header>
    <main>
        <h2>Empréstimos de <?php echo $dadosCliente['nome']; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Valor Total (Sem Juros)</th>
                    <th>Valor Total (Com Juros)</th>
                    <th>Taxa de Juros</th>
                    <th>Quantidade de Parcelas</th>
                    <th>Data de Início</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emprestimos as $emprestimo): ?>
                    <?php
                    // Calcula o valor total do empréstimo com o acréscimo de juros
                    $valor_total_com_juros = $emprestimo['valor_total'] * (1 + $emprestimo['taxa_juros'] / 100);
                    ?>
                    <tr>
                        <td><a href="detalhes_emprestimo.php?id=<?php echo $emprestimo['id']; ?>"><?php echo $emprestimo['id']; ?></a></td>
                        <td><?php echo number_format($emprestimo['valor_total'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($valor_total_com_juros, 2, ',', '.'); ?></td>
                        <td><?php echo $emprestimo['taxa_juros']; ?>%</td>
                        <td><?php echo $emprestimo['quantidade_parcelas']; ?></td>
                        <td><?php echo $emprestimo['data_inicio']; ?></td>
                        <td>
                            <a href="editar_emprestimo.php?id=<?php echo $emprestimo['id']; ?>">Editar</a>
                            <a href="listar_parcelas_emprestimo.php?emprestimo_id=<?php echo $emprestimo['id']; ?>">Ver Parcelas</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <footer>
        <p>&copy; 2025 Sistema de Empréstimos. Todos os direitos reservados.</p>
    </footer>
</body>
</html>