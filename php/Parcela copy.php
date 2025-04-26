<?php
require_once '../config/config.php';

class Parcela
{
    private $conn;

    /**
     * Construtor da classe Parcela
     * Instancia a classe Config e obtém a conexão com o banco de dados
     */
    public function __construct()
    {
        $config = new Config();
        $this->conn = $config->connect();
    }

    /**
     * Método para listar as parcelas de um empréstimo específico
     * @param int $emprestimo_id ID do empréstimo
     * @return array Lista de parcelas
     */
    public function listarParcelasPorEmprestimo($emprestimo_id)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM parcelas_do_emprestimo 
            WHERE emprestimo_id = :emprestimo_id 
            ORDER BY data_vencimento ASC
        ");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para adicionar uma nova parcela
     * @param int $emprestimo_id ID do empréstimo
     * @param int $numero Número da parcela
     * @param string $data_vencimento Data de vencimento da parcela
     * @param float $valor_parcela Valor da parcela
     * @param string $status Status da parcela
     * @return bool Resultado da execução da consulta
     */
    public function adicionarParcela($emprestimo_id, $numero, $data_vencimento, $valor_parcela, $status)
    {
        $stmt = $this->conn->prepare("INSERT INTO parcelas_do_emprestimo (emprestimo_id, numero, data_vencimento, valor_parcela, status) VALUES (:emprestimo_id, :numero, :data_vencimento, :valor_parcela, :status)");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id);
        $stmt->bindValue(':numero', $numero);
        $stmt->bindValue(':data_vencimento', $data_vencimento);
        $stmt->bindValue(':valor_parcela', $valor_parcela);
        $stmt->bindValue(':status', $status);
        return $stmt->execute();
    }

    /**
     * Método para verificar se a parcela está atrasada
     * @param string $data_vencimento Data de vencimento da parcela
     * @return bool True se a parcela está atrasada, False caso contrário
     */
    public function verificarAtraso($data_vencimento)
    {
        return strtotime($data_vencimento) < time();
    }

    /**
     * Método para atualizar o status e o valor da parcela (incluindo a multa, se aplicável)
     * @param int $id ID da parcela
     * @param string $status Status da parcela
     * @param float $valor_parcela Valor da parcela
     * @param string $data_vencimento Data de vencimento da parcela
     * @param float $juros_mora Juros de mora aplicados à parcela
     * @param string|null $data_pagamento Data de pagamento da parcela
     * @param string|null $forma_pagamento Forma de pagamento da parcela
     * @param float $valor_pago Valor pago da parcela
     * @param float $valor_restante Valor restante da parcela
     * @param float $juros_restante Juros de mora aplicados ao valor restante
     * @param string|null $observacoes Observações sobre a parcela
     * @param float $valor_parcela_com_juros Valor da parcela com juros aplicados
     * @return bool Resultado da execução da consulta
     */
    public function atualizarParcela(
        $id,
        $status,
        $valor_parcela,
        $data_vencimento,
        $juros_mora = 0,
        $data_pagamento = null,
        $forma_pagamento = null,
        $valor_pago = 0,
        $valor_restante = 0,
        $juros_restante = 0,
        $observacoes = null,
        $valor_parcela_com_juros = 0
    ) {
        // Calcula as taxas para a parcela
        $valor_juros = 0;
        $valor_juros_restante = 0;

        // Verifica se há juros de mora para a parcela
        if ($juros_mora > 0) {
            $valor_juros = ($valor_parcela * $juros_mora) / 100;
            $valor_parcela_com_juros = $valor_parcela + $valor_juros;
        } else {
            $valor_parcela_com_juros = $valor_parcela;
        }

        // Atualiza o valor restante com base no valor pago
        if ($valor_pago > 0) {
            $valor_restante = $valor_parcela_com_juros - $valor_pago;
            if ($valor_restante < 0) {
                $valor_restante = 0;  // Se o valor restante for negativo, zera o valor
            }
        }

        // Aplica juros de mora sobre o valor restante, se necessário
        if ($juros_restante > 0) {
            $valor_juros_restante = ($valor_restante * $juros_restante) / 100;
            $valor_restante += $valor_juros_restante;
        }

        // Prepara a instrução SQL para atualizar a parcela
        $sql = "UPDATE parcelas_do_emprestimo SET 
                status = :status,
                valor_parcela = :valor_parcela,
                valor_parcela_com_juros = :valor_parcela_com_juros,
                data_vencimento = :data_vencimento,
                juros_mora = :juros_mora,
                data_pagamento = :data_pagamento,
                forma_pagamento = :forma_pagamento,
                valor_pago = :valor_pago,
                valor_restante = :valor_restante,
                observacoes = :observacoes,
                juros_aplicados = :juros_aplicados,
                juros_restante_aplicado = :juros_restante_aplicado
            WHERE id = :id";

        // Prepara a consulta SQL
        $stmt = $this->conn->prepare($sql);

        // Vincula os valores aos parâmetros
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':valor_parcela', $valor_parcela);
        $stmt->bindValue(':valor_parcela_com_juros', $valor_parcela_com_juros);
        $stmt->bindValue(':data_vencimento', $data_vencimento);
        $stmt->bindValue(':juros_mora', $juros_mora);
        $stmt->bindValue(':data_pagamento', $data_pagamento ? $data_pagamento : null); // Verifica se a data de pagamento é nula
        $stmt->bindValue(':forma_pagamento', $forma_pagamento ? $forma_pagamento : null); // Verifica se a forma de pagamento é nula
        $stmt->bindValue(':valor_pago', $valor_pago);
        $stmt->bindValue(':valor_restante', $valor_restante);
        $stmt->bindValue(':observacoes', $observacoes ? $observacoes : null); // Verifica se as observações são nulas
        $stmt->bindValue(':juros_aplicados', $juros_mora);
        $stmt->bindValue(':juros_restante_aplicado', $juros_restante);

        // Executa a consulta SQL
        $stmt->execute();

        // Agora, chamamos o método 'atualizarSaldoEmprestimo' para atualizar o saldo do empréstimo
        $id_emprestimo = $this->getIdEmprestimo($id); // Método para obter o id do empréstimo associado à parcela
        $this->atualizarSaldoEmprestimo($id_emprestimo); // Chama o método para atualizar o saldo do empréstimo

        return true;
    }

    /**
     * Método para obter o id do empréstimo associado à parcela
     * @param int $id_parcela ID da parcela
     * @return int ID do empréstimo
     */
    public function getIdEmprestimo($id_parcela)
    {
        // Consulta para obter o ID do empréstimo associado à parcela
        $query = "SELECT emprestimo_id FROM parcelas_do_emprestimo WHERE id = :id_parcela";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_parcela' => $id_parcela]);

        // Retorna o id do empréstimo, caso encontrado
        return $stmt->fetch(PDO::FETCH_ASSOC)['emprestimo_id'];
    }

    /**
     * Método para calcular e atualizar o valor restante do empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @return string Mensagem de sucesso ou erro
     */
    public function atualizarSaldoEmprestimo($emprestimo_id)
    {
        try {
            // 1. Calcular o total pago das parcelas com status 'PAGO'
            $queryPago = "SELECT SUM(valor_parcela) AS total_pago FROM parcelas_do_emprestimo 
                          WHERE emprestimo_id = :emprestimo_id AND status = 'PAGO'";
            $stmt = $this->conn->prepare($queryPago);
            $stmt->execute(['emprestimo_id' => $emprestimo_id]);
            $totalPago = $stmt->fetch(PDO::FETCH_ASSOC)['total_pago'] ?? 0;

            // 2. Calcular o total pago das parcelas com status 'PAGO PARCIALMENTE'
            $queryParcial = "SELECT SUM(valor_pago) AS total_parcial FROM parcelas_do_emprestimo 
                             WHERE emprestimo_id = :emprestimo_id AND status = 'PAGO PARCIALMENTE'";
            $stmt = $this->conn->prepare($queryParcial);
            $stmt->execute(['emprestimo_id' => $emprestimo_id]);
            $totalParcial = $stmt->fetch(PDO::FETCH_ASSOC)['total_parcial'] ?? 0;

            // 3. Calcular o total pago geral
            $totalPagoGeral = $totalPago + $totalParcial;

            // 4. Buscar o valor total com juros do empréstimo
            $queryEmprestimo = "SELECT valor_total_com_juros FROM emprestimos WHERE id = :id";
            $stmt = $this->conn->prepare($queryEmprestimo);
            $stmt->execute(['id' => $emprestimo_id]);
            $valorTotalComJuros = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total_com_juros'] ?? 0;

            // 5. Calcular o valor restante
            $valorRestante = max(0, $valorTotalComJuros - $totalPagoGeral); // Evita valores negativos

            // 6. Atualizar o campo 'valor_restante_com_juros' na tabela emprestimos
            $queryUpdate = "UPDATE emprestimos SET valor_restante_com_juros = :valor WHERE id = :id";
            $stmt = $this->conn->prepare($queryUpdate);
            $stmt->execute(['valor' => $valorRestante, 'id' => $emprestimo_id]);

            return "Saldo atualizado com sucesso!";

        } catch (PDOException $e) {
            return "Erro ao atualizar saldo: " . $e->getMessage();
        }
    }

    /**
     * Método para obter as informações de uma parcela específica
     * @param int $id ID da parcela
     * @return array Informações da parcela
     */
    public function obterParcela($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM parcelas_do_emprestimo WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para calcular o total valor pago das parcelas de um empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @return float Total valor pago
     */
    public function totalValorPago($emprestimo_id)
    {
        // 1. Calcular o total pago das parcelas com status 'PAGO'
        $queryPago = "SELECT SUM(valor_parcela) AS total_pago FROM parcelas_do_emprestimo 
                      WHERE emprestimo_id = :emprestimo_id AND status = 'PAGO'";
        $stmt = $this->conn->prepare($queryPago);
        $stmt->execute(['emprestimo_id' => $emprestimo_id]);
        $totalPago = $stmt->fetch(PDO::FETCH_ASSOC)['total_pago'] ?? 0;

        // 2. Calcular o total pago das parcelas com status 'PAGO PARCIALMENTE'
        $queryParcial = "SELECT SUM(valor_pago) AS total_parcial FROM parcelas_do_emprestimo 
                         WHERE emprestimo_id = :emprestimo_id AND status = 'PAGO PARCIALMENTE'";
        $stmt = $this->conn->prepare($queryParcial);
        $stmt->execute(['emprestimo_id' => $emprestimo_id]);
        $totalParcial = $stmt->fetch(PDO::FETCH_ASSOC)['total_parcial'] ?? 0;

        // 3. Calcular o total pago geral
        $totalPagoGeral = $totalPago + $totalParcial;

        return $totalPagoGeral;
    }

    /**
     * Método para calcular o valor pago até o momento
     * @param int $emprestimo_id ID do empréstimo
     * @return float Valor pago até o momento
     */
    public function calcularValorPago($emprestimo_id)
    {
        // Prepara a instrução SQL para calcular o valor pago
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(valor_pago), 0) AS valor_pago FROM parcelas_do_emprestimo WHERE emprestimo_id = :emprestimo_id AND status = 'PAGO'");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['valor_pago'];
    }

    /**
     * Método para calcular o valor restante do empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @return float Valor restante do empréstimo
     */
    public function calcularValorRestante($emprestimo_id)
    {
        // Prepara a instrução SQL para calcular o valor restante do empréstimo
        $stmt = $this->conn->prepare("
            SELECT 
                e.valor_total - COALESCE(SUM(p.valor_parcela), 0) AS valor_restante
            FROM 
                emprestimos e
            LEFT JOIN 
                parcelas_do_emprestimo p ON e.id = p.emprestimo_id AND p.status = 'PAGO'
            WHERE 
                e.id = :emprestimo_id
            GROUP BY 
                e.valor_total
        ");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna zero caso o valor restante seja negativo
        return max(0, $result['valor_restante']);
    }

    /**
     * Método para atualizar o valor restante do empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @return bool Resultado da execução da consulta
     */
    public function atualizarValorRestante($emprestimo_id)
    {
        // Prepara a instrução SQL para calcular o valor pago
        $stmt = $this->conn->prepare("
            SELECT 
                e.valor_total, e.taxa_juros, COALESCE(SUM(p.valor_parcela), 0) AS total_pago
            FROM 
                emprestimos e
            LEFT JOIN 
                parcelas_do_emprestimo p ON e.id = p.emprestimo_id AND p.status = 'PAGO'
            WHERE 
                e.id = :emprestimo_id
            GROUP BY 
                e.valor_total, e.taxa_juros
        ");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $valor_total = $result['valor_total'];
        $taxa_juros = $result['taxa_juros'];
        $total_pago = $result['total_pago'];

        // Calcula o valor restante com juros
        $valor_total_com_juros = $valor_total * (1 + $taxa_juros / 100);
        $valor_restante = max(0, $valor_total_com_juros - $total_pago);

        // Prepara a instrução SQL para atualizar o valor restante do empréstimo
        $stmt = $this->conn->prepare("
            UPDATE emprestimos
            SET valor_restante = :valor_restante
            WHERE id = :emprestimo_id
        ");
        $stmt->bindValue(':valor_restante', $valor_restante, PDO::PARAM_STR);
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>