<?php
require_once '../php/Parcela.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $valor_parcela_com_juros = $_POST['valor_parcela_com_juros'];

    // Cria uma nova instância da classe Parcela
    $parcela = new Parcela();
    $conn = $parcela->getConnection();

    // Atualiza o valor_parcela_com_jurosid = :id");
    $stmt = $conn->prepare("UPDATE parcelas_do_emprestimo SET valor_parcela_com_juros = :valor_parcela_com_juros WHERE id = :id");
    $stmt->bindValue(':valor_parcela_com_juros', $valor_parcela_com_juros, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo "Valor atualizado com sucesso.";
}
?>
