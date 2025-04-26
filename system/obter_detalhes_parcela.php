<?php
require_once '../php/Parcela.php';

if (isset($_GET['id']) && isset($_GET['emprestimo_id'])) {
    $parcelaId = $_GET['id'];
    $emprestimoId = $_GET['emprestimo_id'];

    $parcela = new Parcela();
    $dadosParcela = $parcela->obterParcela($parcelaId, $emprestimoId);

    if ($dadosParcela) {
        echo json_encode($dadosParcela);
    } else {
        echo json_encode(['erro' => 'Parcela não encontrada']);
    }
} else {
    echo json_encode(['erro' => 'Parâmetros inválidos']);
}
?>