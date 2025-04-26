<?php
require_once '../php/Emprestimo.php';
require_once '../php/Cliente.php';
require_once '../php/Parcela.php';
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
$emprestimo_id = $_GET['emprestimo_id'];
$cliente_id = $_GET['cliente_id'];
// Obtem o ID do cliente da URL
$cliente = new Cliente();
// Cria uma nova instância da classe Emprestimo
$emprestimo = new Emprestimo();

// Cria uma nova instância da classe Parcela
$parcela = new Parcela();

// Atualiza o valor restante do empréstimo
//$parcela->atualizarValorRestante($emprestimo_id);

// Obtém as informações do empréstimo
$dadosEmprestimo = $emprestimo->obterEmprestimo($emprestimo_id);

// Obtém o ID do cliente
$dadosCliente = $cliente->obterCliente($cliente_id);

// Calcula o valor total do empréstimo com o acréscimo de juros
$valor_total_com_juros = $dadosEmprestimo['valor_total_com_juros'];

// Obtém o valor restante do empréstimo
$valor_restante = $dadosEmprestimo['valor_restante_com_juros'];

// Obtém a lista de parcelas do empréstimo
$parcelas = $parcela->listarParcelasPorEmprestimo($emprestimo_id);

// Calcula o total valor pago das parcelas do empréstimo
$totalPagoGeral = $parcela->totalValorPago($emprestimo_id);

$title = 'Listar Parcelas do Empréstimo';
ob_start();
?>
<article class="container mt-0">
    <header>
        <p class="h4"><i class="fas fa-print"></i>Parcelas do Empréstimo de
            <?php echo htmlspecialchars($dadosCliente['nome']); ?>
        </p>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <p class="h5">Valor Total: <?php echo number_format($valor_total_com_juros, 2, ',', '.'); ?></p>
            </div>
            <div class="col-md-4">
                <p class="h5">Valor Total Restante: <?php echo number_format($valor_restante, 2, ',', '.'); ?></p>
            </div>
            <div class="col-md-4">
                <p class="h5">Valor Total Pago: <?php echo number_format($totalPagoGeral, 2, ',', '.'); ?></p>
            </div>

        </div>
        <hr>
    </header>

    <section class="mb-4">
        <button id="botao-imprimir" class="btn btn-primary"> <i class="fas fa-print"></i> Imprimir Parcelas</button>
        <a href="listar_emprestimos_cliente.php?cliente_id=<?php echo $dadosEmprestimo['cliente_id']; ?>"
            class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar para Empréstimos</a>

    </section>

    <section class="table-responsive" id="tabela-parcelas">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>N.º</th>
                    <th>Vencimento</th>
                    <th>Parcela</th>
                    <th>Parcela/Acressimos</th>
                    <th>Valor Pago</th>
                    <th>Valor Restante</th>
                    <th>Status</th>
                    <th class="nao-imprimir">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parcelas as $parcela): ?>
                    <tr>
                        <td><?php echo $parcela['numero']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($parcela['data_vencimento'])); ?></td>
                        <td><?php echo 'R$ ' . number_format($parcela['valor_parcela'] ?? 0, 2, ',', '.'); ?></td>
                        <td><?php echo 'R$ ' . number_format($parcela['valor_parcela_com_juros'] ?? 0, 2, ',', '.'); ?></td>
                        <td><?php echo $parcela['status'] == 'PAGO' ? 'R$ ' . number_format($parcela['valor_pago'] ?? 0, 2, ',', '.') : 'R$ ' . number_format($parcela['valor_pago'] ?? 0, 2, ',', '.'); ?>
                        </td>
                        <td><?php echo $parcela['status'] == 'PAGO' ? 'R$ 0,00' : 'R$ ' . number_format($parcela['valor_restante'] ?? 0, 2, ',', '.'); ?>
                        </td>
                        <td>
                            <?php
                            if ($parcela['status'] == 'PAGO') {
                                echo '<span class="badge badge-success">' . ucfirst($parcela['status']) . '</span>';
                            } elseif ($parcela['status'] == 'PENDENTE') {
                                echo '<span class="badge badge-warning">' . ucfirst($parcela['status']) . '</span>';
                            } elseif ($parcela['status'] == 'PAGO PARCIALMENTE') {
                                echo '<span class="badge badge-info">' . ucfirst($parcela['status']) . '</span>';
                            } else {
                                echo '<span class="badge badge-danger">' . ucfirst($parcela['status']) . '</span>';
                            }
                            ?>
                        </td>
                        <td class="nao-imprimir">
                            <?php if ($parcela['status'] == 'PAGO' || $parcela['status'] == 'PAGO PARCIALMENTE'): ?>
                                <button class="btn btn-info btn-sm abrirModalParcela" data-toggle="modal"
                                    data-target="#detalhesParcelaModal" data-id="<?php echo $parcela['id']; ?>"
                                    data-emprestimo-id="<?php echo $emprestimo_id; ?>">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            <?php endif; ?>

                            <a href="editar_parcela.php?id=<?php echo $parcela['id']; ?>&emprestimo_id=<?php echo $emprestimo_id; ?>&cliente_id=<?php echo $cliente_id; ?>"
                                class="btn btn-warning btn-sm"><i class="far fa-edit"></i> Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</article>

<!-- Modal para detalhes da parcela -->
<div class="modal fade" id="detalhesParcelaModal" tabindex="-1" role="dialog"
    aria-labelledby="detalhesParcelaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesParcelaModalLabel">Detalhes da Parcela </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Detalhes</th>
                            <th>Informações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Número</th>
                            <td><span id="detalhesId"></span></td>
                        </tr>
                        <tr>
                            <th>Vencimento</th>
                            <td><span id="detalhesVencimento"></span></td>
                        </tr>
                        <tr>
                            <th>Pagamento</th>
                            <td><span id="detalhesPagamento"></span></td>
                        </tr>
                        <tr>
                            <th>Parcela</th>
                            <td><span id="detalhesParcela"></span></td>
                        </tr>
                        <tr id="rowParcelaAcressimos">
                            <th>Parcela/Acressimos</th>
                            <td><span id="detalhesParcelaAcressimos"></span></td>
                        </tr>
                        <tr id="rowValorPago">
                            <th>Valor Pago</th>
                            <td><span id="detalhesValorPago"></span></td>
                        </tr>
                        <tr id="rowValorRestante">
                            <th>Valor Restante</th>
                            <td><span id="detalhesValorRestante"></span></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span id="detalhesStatus"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button class="btn btn-info btn-sm" id="imprimirDetalhesParcela">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Carregar o jQuery primeiro -->
<script src="../js/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.abrirModalParcela').on('click', function () {
            var parcelaId = $(this).data('id');
            var emprestimoId = $(this).data('emprestimo-id');

            $.ajax({
                url: 'obter_detalhes_parcela.php',
                type: 'GET',
                data: { id: parcelaId, emprestimo_id: emprestimoId },
                dataType: 'json',
                success: function (parcela) {
                    if (parcela.erro) {
                        alert('Erro: ' + parcela.erro);
                        return;
                    }

                    $('#detalhesId').text(parcela.numero); // Update to show 'numero'
                    $('#detalhesVencimento').text(new Date(parcela.data_vencimento).toLocaleDateString('pt-BR'));
                    $('#detalhesPagamento').text(parcela.data_pagamento ? new Date(parcela.data_pagamento).toLocaleDateString('pt-BR') : 'N/A');
                    $('#detalhesParcela').text('R$ ' + parseFloat(parcela.valor_parcela).toFixed(2).replace('.', ','));

                    if (parseFloat(parcela.valor_parcela_com_juros) > parseFloat(parcela.valor_parcela)) {
                        $('#rowParcelaAcressimos').show();
                        $('#detalhesParcelaAcressimos').text('R$ ' + parseFloat(parcela.valor_parcela_com_juros).toFixed(2).replace('.', ','));
                    } else {
                        $('#rowParcelaAcressimos').hide();
                    }

                    if (parseFloat(parcela.valor_pago) === 0) {
                        $('#rowValorPago').hide();
                    } else {
                        $('#rowValorPago').show();
                        $('#detalhesValorPago').text('R$ ' + parseFloat(parcela.valor_pago).toFixed(2).replace('.', ','));
                    }

                    if (parseFloat(parcela.valor_restante) === 0) {
                        $('#rowValorRestante').hide();
                    } else {
                        $('#rowValorRestante').show();
                        $('#detalhesValorRestante').text('R$ ' + parseFloat(parcela.valor_restante).toFixed(2).replace('.', ','));
                    }

                    $('#detalhesStatus').text(parcela.status);

                    // Abre a modal manualmente se necessário
                    $('#detalhesParcelaModal').modal('show');
                },
                error: function () {
                    alert('Erro ao buscar detalhes da parcela.');
                }
            });
        });

        $('#imprimirDetalhesParcela').on('click', function () {
            var printContents = document.querySelector('.modal-body').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload(); // Reload the page to restore the original content
        });
    });


</script>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>