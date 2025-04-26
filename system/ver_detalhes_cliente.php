<?php
require_once '../php/Cliente.php';

// Verifica se o ID do cliente foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Cria uma nova instância da classe Cliente
    $cliente = new Cliente();

    // Obtém os detalhes do cliente
    $dadosCliente = $cliente->VerDetalheCliente($id);

    // Retorna os dados do cliente em formato JSON
    echo json_encode($dadosCliente);
}
?>
