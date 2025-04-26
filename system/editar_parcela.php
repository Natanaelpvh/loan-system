<?php
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

// Obtém o ID da parcela e do empréstimo da URL
$id = $_GET['id'];
$emprestimo_id = $_GET['emprestimo_id'];
$cliente_id = $_GET['cliente_id'];

// Cria uma nova instância da classe Parcela
$parcela = new Parcela();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor_parcela = $_POST['valor_parcela']; // Valor editado da parcela
    $valor_parcela_com_juros = $_POST['valor_parcela_com_juros']; // Novo campo
    $juros_mora = $_POST['juros_mora'];
    $data_vencimento = $_POST['data_vencimento'];
    $data_pagamento = $_POST['data_pagamento'];
    $forma_pagamento = $_POST['forma_pagamento'];
    $valor_pago = $_POST['valor_pago'];
    $valor_restante = $_POST['valor_restante'];
    $juros_restante = $_POST['juros_restante']; // Juros de mora para valor restante
    $observacoes = $_POST['observacoes'];
    $status = $_POST['status'];
    $cliente_id = $_POST['cliente_id']; // Pegando o cliente_id do POST

    // Atualiza os dados da parcela
    if ($parcela->atualizarParcela($id, $status, $valor_parcela, $data_vencimento, $juros_mora, $data_pagamento, $forma_pagamento, $valor_pago, $valor_restante, $juros_restante, $observacoes, $valor_parcela_com_juros)) {
        // Redireciona para a página de listagem de parcelas do empréstimo
        // Verifica a URL gerada antes de redirecionar
        $redirect_url = 'listar_parcelas_emprestimo.php?emprestimo_id=' . urlencode($emprestimo_id) . '&cliente_id=' . urlencode($cliente_id);
        echo "<script>console.log('Redirecionando para: $redirect_url');</script>";
        header('Location: ' . $redirect_url);
        exit();
    } else {
        $erro = "Erro ao atualizar a parcela.";
    }
}

// Obtém as informações da parcela
$dadosParcela = $parcela->obterParcela($id);

// Verifica se a parcela foi encontrada
$title = 'Editar Parcela do Emprestimo';
ob_start();
?>

<main class="container mt-5">

    <h2 class="mb-4">Editar Parcela do Emprestimo</h2>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="editar_parcela.php?id=<?php echo $id; ?>&emprestimo_id=<?php echo $emprestimo_id; ?>"
        class="needs-validation" novalidate>

        <input type="hidden" name="emprestimo_id" value="<?php echo $emprestimo_id; ?>">

        <div class="container">
            <div class="row">
                <!-- Valor da Parcela (Não editável) -->
                <div class="col-md-3">
                    <label for="valor_parcela" class="form-label"><strong>Valor da Parcela:</strong></label>
                    <input type="number" id="valor_parcela" name="valor_parcela" class="form-control is-valid"
                        value="<?php echo number_format($dadosParcela['valor_parcela'] ?? 0, 2, '.', ''); ?>"
                        step="0.01" readonly>
                </div>
                <!-- Valor da Parcela (Não editável) -->
                <div class="col-md-3">
                    <label for="valor_parcela_com_juros" class="form-label"><strong>Parcela Com Juros de Mora:</strong></label>
                    <input type="number" id="valor_parcela_com_juros" name="valor_parcela_com_juros"
                        class="form-control is-valid"
                        value="<?php echo number_format($dadosParcela['valor_parcela_com_juros'] ?? 0, 2, '.', ''); ?>"
                        step="0.01" readonly>
                </div>

                <!-- Juros de Mora -->
                <div class="col-md-3">
                    <label for="juros_mora" class="form-label"><strong>Juros de Mora (em %):</strong></label>
                    <input type="number" id="juros_mora" name="juros_mora" class="form-control"
                        value="<?php echo number_format($dadosParcela['juros_mora'] ?? 0, 2, '.', ''); ?>" step="0.01">
                </div>

                <!-- Data de Vencimento (Não editável) -->
                <div class="col-md-3">
                    <label for="data_vencimento" class="form-label"><strong>Data de Vencimento:</strong></label>
                    <input type="date" id="data_vencimento" name="data_vencimento" class="form-control"
                        value="<?php echo $dadosParcela['data_vencimento']; ?>" readonly>
                </div>
            </div>

            <div class="row">
                <!-- Data do Pagamento -->
                <div class="col-md-3">
                    <label for="data_pagamento" class="form-label"><strong>Data do Pagamento:</strong></label>
                    <input type="date" id="data_pagamento" name="data_pagamento" class="form-control"
                        value="<?php echo $dadosParcela['data_pagamento']; ?>" required>
                </div>

                <!-- Forma de Pagamento -->
                <div class="col-md-3">
                    <label for="forma_pagamento" class="form-label"><strong>Forma de Pagamento:</strong></label>
                    <select id="forma_pagamento" name="forma_pagamento" class="form-control">
                        <option value="">Selecione...</option>
                        <option value="PIX" <?php if ($dadosParcela['forma_pagamento'] == 'PIX')
                            echo 'selected'; ?>>PIX
                        </option>
                        <option value="BOLETO" <?php if ($dadosParcela['forma_pagamento'] == 'BOLETO')
                            echo 'selected'; ?>>Boleto</option>
                        <option value="TRANSFERÊNCIA" <?php if ($dadosParcela['forma_pagamento'] == 'TRANSFERÊNCIA')
                            echo 'selected'; ?>>Transferência</option>
                        <option value="DINHEIRO" <?php if ($dadosParcela['forma_pagamento'] == 'DINHEIRO')
                            echo 'selected'; ?>>Dinheiro</option>
                    </select>
                </div>

                <!-- Valor Pago -->
                <div class="col-md-3">
                    <label for="valor_pago" class="form-label"><strong>Valor Pago:</strong></label>
                    <input type="number" id="valor_pago" name="valor_pago" class="form-control"
                        value="<?php echo number_format($dadosParcela['valor_pago'] ?? 0, 2, '.', ''); ?>" step="0.01">
                </div>
            </div>

            <div class="row">
                <!-- Valor Restante (Calculado automaticamente) -->
                <div class="col-md-2">
                    <label for="valor_restante" class="form-label"><strong>Valor Restante:</strong></label>
                    <input type="number" id="valor_restante" name="valor_restante" class="form-control is-invalid"
                        value="<?php echo number_format($dadosParcela['valor_restante'] ?? 0, 2, '.', ''); ?>"
                        step="0.01" readonly>
                </div>

                <!-- Juros de Mora para o Valor Restante -->
                <div class="col-md-4">
                    <label for="juros_restante" class="form-label">Juros de Mora para o Valor Restante (em %):</label>
                    <input type="number" id="juros_restante" name="juros_restante" class="form-control"
                        value="<?php echo number_format($dadosParcela['juros_restante_aplicado'] ?? 0, 2, '.', ''); ?>"
                        step="0.01">
                </div>

                <!-- Status -->
                <div class="col-md-3">
                    <label for="status" class="form-label"><strong>Status:</strong></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="PENDENTE" <?php if ($dadosParcela['status'] == 'PENDENTE')
                            echo 'selected'; ?>>
                            PENDENTE</option>
                        <option value="PAGO" <?php if ($dadosParcela['status'] == 'PAGO')
                            echo 'selected'; ?>>PAGO
                        </option>
                        <option value="ATRASADO" <?php if ($dadosParcela['status'] == 'ATRASADO')
                            echo 'selected'; ?>>
                            ATRASADO</option>
                        <option value="PAGO PARCIALMENTE" <?php if ($dadosParcela['status'] == 'PAGO PARCIALMENTE')
                            echo 'selected'; ?>>PAGO PARCIALMENTE</option>
                    </select>
                </div>
            </div>
            <div class="row">

                <!-- Observações -->
                <div class="col-md-12">
                    <label for="observacoes" class="form-label"><strong>Observações:</strong></label>
                    <textarea id="observacoes" name="observacoes" class="form-control"
                        rows="3"><?php echo $dadosParcela['observacoes']; ?></textarea>
                </div>
            </div>
            <div class="row">
            <div class="col-md-6">
                    <div class="text-center mt-4">
                        <button id="atualizar" type="submit" class="btn btn-outline-primary"><i class="fa fa-sync"></i>
                            Atualizar</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center mt-4">
                        <a href="listar_parcelas_emprestimo.php?emprestimo_id=<?php echo $emprestimo_id; ?>&cliente_id=<?php echo $cliente_id; ?>"
                            class="btn btn-outline-warning">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
               
            </div>
        </div>
        <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
    </form>
    <div id="contador" class="mt-4"></div>
    <div id="mensagem" class="mt-4"></div> <!-- Div para exibir a mensagem de erro ou sucesso -->
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    /**
     * Classe para gerenciar a atualização das parcelas com base nos juros de mora.
     */
    class AtualizadorParcela {
        /**
         * Construtor da classe AtualizadorParcela
         * @param {number} id - ID da parcela
         * @param {string} updatedAt - Data da última atualização (string formatada como data)
         * @param {number} jurosMora - Percentual do juros de mora
         * @param {string} status - Status da parcela (ex: "ATRASADO", "PAGO")
         */
        constructor(id, updatedAt, jurosMora, status) {
            this.id = id;
            this.updatedAt = new Date(updatedAt); // Converte a string de data para um objeto Date
            this.jurosMora = parseFloat(jurosMora) || 0; // Converte juros para número, caso não seja válido assume 0
            this.status = status;
            this.nextUpdate = new Date(this.updatedAt.getTime() + 24 * 60 * 60 * 1000); // Próxima atualização em 24h
           //Teste de tempo de atualizaçãoo           
            //this.nextUpdate = new Date(this.updatedAt.getTime() + 1000); // Próxima atualização em 24h
            this.init();
        }

        /**
         * Método para inicializar a classe e configurar atualizações periódicas
         */
        init() {
            this.updateContador(); // Atualiza o contador de tempo
            setInterval(() => this.updateContador(), 1000); // Atualiza o contador a cada segundo
            this.verificarAtualizacao(); // Verifica se é necessário atualizar a parcela imediatamente
        }

        /**
         * Verifica se a atualização do valor da parcela é necessária ao iniciar
         */
        verificarAtualizacao() {
            const now = new Date();
            if (this.status === "ATRASADO" && this.jurosMora > 0 && now >= this.nextUpdate) {
                const diasPassados = Math.floor((now - this.updatedAt) / (24 * 60 * 60 * 1000)); // Calcula dias de atraso
                for (let i = 0; i < diasPassados; i++) {
                    this.atualizarValorParcelaComJuros(); // Aplica os juros para cada dia de atraso
                }
            }
        }

        /**
         * Atualiza o valor da parcela aplicando os juros de mora, via AJAX
         */
        atualizarValorParcelaComJuros() {
            const valorParcelaComJuros = parseFloat($("#valor_parcela_com_juros").val()) || 0;
            const now = new Date();
            
            if (this.status === "ATRASADO" && this.jurosMora > 0 && now >= this.nextUpdate) {
                const novoValorParcelaComJuros = valorParcelaComJuros + (valorParcelaComJuros * (this.jurosMora / 100));
                
                $.ajax({
                    url: 'atualizar_valor_parcela_com_juros.php',
                    type: 'POST',
                    data: {
                        id: this.id,
                        valor_parcela_com_juros: novoValorParcelaComJuros.toFixed(2)
                    },
                    success: (response) => {
                        $('#mensagem').html('<div class="alert alert-success" role="alert">' + response + '</div>');
                        $("#valor_parcela_com_juros").val(novoValorParcelaComJuros.toFixed(2));
                        this.nextUpdate = new Date(this.nextUpdate.getTime() + 24 * 60 * 60 * 1000); // Define nova data de atualização
                    },
                    error: () => {
                        $('#mensagem').html('<div class="alert alert-danger" role="alert">Erro ao atualizar o valor.</div>');
                    }
                });
            }
        }

        /**
         * Atualiza o contador regressivo para a próxima atualização
         */
        updateContador() {
            const now = new Date();
            const timeDiff = this.nextUpdate - now;
            
            if (this.jurosMora > 0 && this.status === "ATRASADO") {
                if (timeDiff <= 0) {
                    setTimeout(() => {
                        this.atualizarValorParcelaComJuros();
                    }, 1000);
                } else {
                    const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                    $('#contador').text('Próxima atualização em: ' + hours + 'h ' + minutes + 'm ' + seconds + 's');
                }
            } else {
                $('#contador').text('');
            }
        }
    }

    /**
     * Quando o documento estiver pronto, inicializa o sistema de atualização de parcelas
     */
    $(document).ready(function () {
        const id = <?php echo $id; ?>;
        const updatedAt = "<?php echo $dadosParcela['updated_at']; ?>";
        const jurosMora = $("#juros_mora").length ? parseFloat($("#juros_mora").val()) || 0 : 0;
        const status = $("#status").length ? $("#status").val() : "PAGO";

        new AtualizadorParcela(id, updatedAt, jurosMora, status);

        const valorParcelaInput = $("#valor_parcela");
        const valorParcelaComJurosInput = $("#valor_parcela_com_juros");
        const valorPagoInput = $("#valor_pago");
        const valorRestanteInput = $("#valor_restante");

        /**
         * Atualiza o valor restante automaticamente ao alterar o valor pago
         */
        valorPagoInput.on("input", function () {
            const valorParcelaComJuros = parseFloat(valorParcelaComJurosInput.val()) || 0;
            const valorParcela = parseFloat(valorParcelaInput.val()) || 0;
            const valorBase = valorParcelaComJuros > 0 ? valorParcelaComJuros : valorParcela;
            const valorPago = parseFloat(valorPagoInput.val()) || 0;
            const valorRestante = Math.max(0, valorBase - valorPago);
            valorRestanteInput.val(valorRestante.toFixed(2));
        });

        /**
         * Habilita o botão de atualização quando há mudanças nos campos
         */
        function toggleAtualizarButton() {
            $("#atualizar").prop("disabled", false);
        }

        $("#atualizar").prop("disabled", true);
        $("#juros_mora, #data_pagamento, #valor_pago, #juros_restante").on("input", toggleAtualizarButton);
    });
</script>


<?php
$content = ob_get_clean();
include '../includes/template.php';
?>