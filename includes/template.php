<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Sistema de Empréstimos</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <!-- FontAwesome para ícones -->
    <link href="../css/fontawesome/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

    <div class="wrapper">
        <?php include 'menu.php'; ?> <!-- Corrigido o include -->

        <main>
            <div class="container-fluid">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <footer class="bg-light text-center py-3 mt-4">
        <p>&copy; 2025 Sistema de Empréstimos. Todos os direitos reservados.</p>
    </footer>
    <?php // Obtém a URL atual
    $urlAtual = $_SERVER['REQUEST_URI']; ?>
    <!-- Carregar o jQuery primeiro -->
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script src="../js/jquery.mask.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {

            $('.detalhes-cliente').on('click', function (e) {
                e.preventDefault();
                var clienteId = $(this).data('id');
                $.ajax({
                    url: 'ver_detalhes_cliente.php',
                    type: 'GET',
                    data: { id: clienteId },
                    success: function (data) {
                        var cliente = JSON.parse(data);
                        $('#detalhesNome').text(cliente.nome);
                        $('#detalhesCPF').text(cliente.cpf);
                        $('#detalhesEmail').text(cliente.email);
                        $('#detalhesTelefone').text(cliente.telefone);
                        $('#detalhesEndereco').text(cliente.rua + ', ' + cliente.numero + ', ' + cliente.bairro + ', ' + cliente.cidade + ', ' + cliente.estado + ', ' + cliente.cep);
                        $('#detalhesStatus').text(cliente.status);
                        $('#detalhesClienteModal').modal('show');
                    },
                    error: function () {
                        alert('Erro ao buscar detalhes do cliente.');
                    }
                });
            });
        });
    </script>
   
 
    <?php
    // Obtém a URL atual
    $urlAtual = $_SERVER['REQUEST_URI'];

    // Verifica se a URL contém "listar_parcelas_emprestimo.php" e se tem os parâmetros esperados
    if (strpos($urlAtual, 'listar_parcelas_emprestimo.php') !== false && isset($_GET['emprestimo_id']) && isset($_GET['cliente_id'])):
        ?>
        <script>
            // Evita conflitos do jQuery
            var jq = jQuery.noConflict();

            // Variáveis PHP passadas corretamente para JavaScript
            var valorTotal = "<?php echo number_format($valor_total_com_juros, 2, ',', '.'); ?>";
            var valorRestante = "<?php echo number_format($valor_restante, 2, ',', '.'); ?>";

            jq(document).ready(function () {
                jq("#botao-imprimir").on("click", function () {
                    var conteudo = jq("#tabela-parcelas").html();
                    var janelaImpressao = window.open("", "", "width=800,height=600");

                    if (janelaImpressao) {
                        janelaImpressao.document.write(`
                        <html>
                        <head>
                            <title>Imprimir Parcelas</title>
                            <style>
                                .nao-imprimir { display: none; }
                            </style>
                        </head>
                        <body>
                            <h2>Valor Total: ${valorTotal}</h2>
                            <h2>Valor Restante: ${valorRestante}</h2>
                            ${conteudo}
                        </body>
                        </html>
                    `);
                    janelaImpressao.document.close();

                    // Aguarda o carregamento do conteúdo antes de imprimir
                    janelaImpressao.onload = function () {
                        janelaImpressao.print();
                    };
                } else {
                    alert("Erro ao abrir a janela de impressão. Verifique se os pop-ups estão bloqueados.");
                }
            });
        });
    </script>
    <?php
    endif;
    ?>

</body>

</html>