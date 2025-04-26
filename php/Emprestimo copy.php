<?php
require_once '../config/config.php';

class Emprestimo
{
    private $conn;

    /**
     * Construtor da classe Emprestimo
     * Instancia a classe Config e obtém a conexão com o banco de dados
     */
    public function __construct()
    {
        $config = new Config();
        $this->conn = $config->connect();
    }

    /**
     * Método para listar todos os empréstimos
     * @return array Lista de empréstimos
     */
    public function listarEmprestimos()
    {
        $stmt = $this->conn->prepare("SELECT e.id, c.nome AS cliente, e.valor_total, e.taxa_juros, e.quantidade_parcelas, e.data_inicio FROM emprestimos e JOIN clientes c ON e.cliente_id = c.id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para listar os empréstimos de um cliente específico
     * @param int $cliente_id ID do cliente
     * @return array Lista de empréstimos do cliente
     */
    public function listarEmprestimosPorCliente($cliente_id)
    {
        $stmt = $this->conn->prepare("SELECT id, valor_total, valor_total_com_juros, taxa_juros, quantidade_parcelas, data_inicio, valor_restante_com_juros, tipo_juros FROM emprestimos WHERE cliente_id = :cliente_id");
        $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para obter um empréstimo pelo ID
     * @param int $id ID do empréstimo
     * @return array Informações do empréstimo
     */
    public function obterEmprestimo($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM emprestimos WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para adicionar um novo empréstimo
     * @param int $cliente_id ID do cliente
     * @param float $valor_total Valor total do empréstimo
     * @param float $taxa_juros Taxa de juros do empréstimo
     * @param int $quantidade_parcelas Quantidade de parcelas do empréstimo
     * @param string $data_inicio Data de início do empréstimo
     * @param int $dia_vencimento Dia de vencimento das parcelas
     * @param string $tipo_juros Tipo de juros (simples ou compostos)
     * @param string $frequencia_juros Frequência dos juros (mensal ou anual)
     * @return bool Resultado da execução da consulta
     */
    public function adicionarEmprestimo($cliente_id, $valor_total, $taxa_juros, $quantidade_parcelas, $data_inicio, $dia_vencimento, $tipo_juros, $frequencia_juros)
    {
        // Converte valores para números
        $valor_total = (float) $valor_total;
        $quantidade_parcelas = (int) $quantidade_parcelas;

        // Calcula o valor total com juros, dependendo do tipo de juros e da frequência
        if ($tipo_juros === 'simples') {
            if ($frequencia_juros === 'mensal') {
                // Juros simples mensal
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros simples anual
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * ($quantidade_parcelas / 12));
            }
        } elseif ($tipo_juros === 'compostos') {
            if ($frequencia_juros === 'mensal') {
                // Juros compostos mensal
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros compostos anual
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), ($quantidade_parcelas / 12));
            }
        }

        // Calcula o valor das parcelas
        $valor_parcela = round($valor_total_com_juros / $quantidade_parcelas, 2);
        $valor_parcela_ajustada = round($valor_total_com_juros - ($valor_parcela * ($quantidade_parcelas - 1)), 2);

        // Inserir empréstimo no banco de dados
        $stmt = $this->conn->prepare("INSERT INTO emprestimos (cliente_id, valor_total, taxa_juros, quantidade_parcelas, data_inicio, valor_total_com_juros, dia_vencimento, tipo_juros, frequencia_juros) 
                                  VALUES (:cliente_id, :valor_total, :taxa_juros, :quantidade_parcelas, :data_inicio, :valor_total_com_juros, :dia_vencimento, :tipo_juros, :frequencia_juros)");
        $stmt->bindValue(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->bindValue(':valor_total', $valor_total, PDO::PARAM_STR);
        $stmt->bindValue(':taxa_juros', $taxa_juros, PDO::PARAM_STR);
        $stmt->bindValue(':quantidade_parcelas', $quantidade_parcelas, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $data_inicio, PDO::PARAM_STR);
        $stmt->bindValue(':valor_total_com_juros', $valor_total_com_juros, PDO::PARAM_STR);
        $stmt->bindValue(':dia_vencimento', $dia_vencimento, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_juros', $tipo_juros, PDO::PARAM_STR);
        $stmt->bindValue(':frequencia_juros', $frequencia_juros, PDO::PARAM_STR);
        $stmt->execute();

        // Obtém o ID do empréstimo recém-criado
        $emprestimo_id = $this->conn->lastInsertId();

        // Criar as parcelas do empréstimo
        for ($i = 1; $i <= $quantidade_parcelas; $i++) {
            $data_vencimento = date('Y-m-d', strtotime("+$i month", strtotime($data_inicio)));
            $valor_parcela_final = ($i == $quantidade_parcelas) ? $valor_parcela_ajustada : $valor_parcela;

            // Inserir parcela no banco de dados
            $stmt = $this->conn->prepare("INSERT INTO parcelas_do_emprestimo (emprestimo_id, numero, data_vencimento, valor_parcela, status, valor_pago) 
                                      VALUES (:emprestimo_id, :numero, :data_vencimento, :valor_parcela, 'pendente', 0)");
            $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
            $stmt->bindValue(':numero', $i, PDO::PARAM_INT);
            $stmt->bindValue(':data_vencimento', $data_vencimento, PDO::PARAM_STR);
            $stmt->bindValue(':valor_parcela', $valor_parcela_final, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Redireciona para a listagem de empréstimos do cliente
        header('Location: listar_emprestimos_cliente.php?cliente_id=' . $cliente_id);
        exit;
    }

    /**
     * Método para buscar um empréstimo pelo ID
     * @param int $id ID do empréstimo
     * @return array Informações do empréstimo
     */
    public function getEmprestimoById($id)
    {
        // Prepara a instrução SQL para buscar um empréstimo por ID
        $sql = "SELECT * FROM emprestimos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Executa a consulta
        $stmt->execute();

        // Retorna o resultado como um array associativo
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para buscar as parcelas de um empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @return array Lista de parcelas do empréstimo
     */
    public function getParcelasByEmprestimoId($emprestimo_id)
    {
        // Prepara a instrução SQL para buscar as parcelas de um empréstimo
        $sql = "SELECT * FROM parcelas_do_emprestimo WHERE emprestimo_id = :emprestimo_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);

        // Executa a consulta
        $stmt->execute();

        // Retorna o resultado como um array de parcelas
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para atualizar as informações de um empréstimo
     * @param int $id ID do empréstimo
     * @param float $valor_total Valor total do empréstimo
     * @param float $taxa_juros Taxa de juros do empréstimo
     * @param int $quantidade_parcelas Quantidade de parcelas do empréstimo
     * @param string $data_inicio Data de início do empréstimo
     * @param int $dia_vencimento Dia de vencimento das parcelas
     * @param string $tipo_juros Tipo de juros (simples ou compostos)
     * @param string $frequencia_juros Frequência dos juros (mensal ou anual)
     * @return bool Resultado da execução da consulta
     */
    public function atualizarEmprestimo($id, $valor_total, $taxa_juros, $quantidade_parcelas, $data_inicio, $dia_vencimento, $tipo_juros, $frequencia_juros)
    {
        // Verifica se o empréstimo existe
        $emprestimo = $this->getEmprestimoById($id);
        if (!$emprestimo) {
            // Se o empréstimo não existe, retorna falso
            return false;
        }

        // Calcula o valor total com juros, dependendo do tipo de juros e da frequência
        if ($tipo_juros === 'simples') {
            if ($frequencia_juros === 'mensal') {
                // Juros simples mensal
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros simples anual
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * ($quantidade_parcelas / 12));
            }
        } elseif ($tipo_juros === 'compostos') {
            if ($frequencia_juros === 'mensal') {
                // Juros compostos mensal
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros compostos anual
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), ($quantidade_parcelas / 12));
            }
        }

        // Prepara a instrução SQL para atualizar o empréstimo
        $sql = "UPDATE emprestimos SET 
            valor_total = :valor_total,
            taxa_juros = :taxa_juros,
            quantidade_parcelas = :quantidade_parcelas,
            data_inicio = :data_inicio,
            dia_vencimento = :dia_vencimento,
            tipo_juros = :tipo_juros,
            frequencia_juros = :frequencia_juros,
            valor_total_com_juros = :valor_total_com_juros
        WHERE id = :id";

        // Prepara a consulta SQL
        $stmt = $this->conn->prepare($sql);

        // Vincula os valores aos parâmetros
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':valor_total', $valor_total);
        $stmt->bindValue(':taxa_juros', $taxa_juros);
        $stmt->bindValue(':quantidade_parcelas', $quantidade_parcelas, PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio', $data_inicio);
        $stmt->bindValue(':dia_vencimento', $dia_vencimento, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_juros', $tipo_juros);
        $stmt->bindValue(':frequencia_juros', $frequencia_juros);
        $stmt->bindValue(':valor_total_com_juros', $valor_total_com_juros);

        // Executa a consulta SQL
        $resultado = $stmt->execute();

        // Após atualizar o empréstimo, chama o método para atualizar as parcelas
        if ($resultado) {
            $this->atualizarParcelas($id, $quantidade_parcelas, $valor_total, $taxa_juros, $data_inicio, $tipo_juros, $frequencia_juros);
        }

        return $resultado;
    }

    /**
     * Método para atualizar as parcelas após a alteração no empréstimo
     * @param int $emprestimo_id ID do empréstimo
     * @param int $quantidade_parcelas Quantidade de parcelas do empréstimo
     * @param float $valor_total Valor total do empréstimo
     * @param float $taxa_juros Taxa de juros do empréstimo
     * @param string $data_inicio Data de início do empréstimo
     * @param string $tipo_juros Tipo de juros (simples ou compostos)
     * @param string $frequencia_juros Frequência dos juros (mensal ou anual)
     */
    public function atualizarParcelas($emprestimo_id, $quantidade_parcelas, $valor_total, $taxa_juros, $data_inicio, $tipo_juros, $frequencia_juros)
    {
        // Verifica se já existem parcelas associadas ao empréstimo
        $parcelas = $this->getParcelasByEmprestimoId($emprestimo_id);
        if (!$parcelas) {
            // Se não houver parcelas, retorna falso
            return false;
        }
        // Remove as parcelas antigas
        $stmt = $this->conn->prepare("DELETE FROM parcelas_do_emprestimo WHERE emprestimo_id = :emprestimo_id");
        $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
        $stmt->execute();

        // Calcula o valor total com juros, dependendo do tipo de juros e da frequência
        if ($tipo_juros === 'simples') {
            if ($frequencia_juros === 'mensal') {
                // Juros simples mensal
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros simples anual
                $valor_total_com_juros = $valor_total * (1 + ($taxa_juros / 100) * ($quantidade_parcelas / 12));
            }
        } elseif ($tipo_juros === 'compostos') {
            if ($frequencia_juros === 'mensal') {
                // Juros compostos mensal
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), $quantidade_parcelas);
            } elseif ($frequencia_juros === 'anual') {
                // Juros compostos anual
                $valor_total_com_juros = $valor_total * pow((1 + $taxa_juros / 100), ($quantidade_parcelas / 12));
            }
        }

        // Calcula o valor das parcelas
        $valor_parcela = round($valor_total_com_juros / $quantidade_parcelas, 2);
        $valor_parcela_ajustada = round($valor_total_com_juros - ($valor_parcela * ($quantidade_parcelas - 1)), 2);

        // Criação das novas parcelas
        for ($i = 1; $i <= $quantidade_parcelas; $i++) {
            $data_vencimento = date('Y-m-d', strtotime("+$i month", strtotime($data_inicio)));
            $valor_parcela_final = ($i == $quantidade_parcelas) ? $valor_parcela_ajustada : $valor_parcela;

            $stmt = $this->conn->prepare("INSERT INTO parcelas_do_emprestimo (emprestimo_id, numero, data_vencimento, valor_parcela, status, valor_pago) VALUES (:emprestimo_id, :numero, :data_vencimento, :valor_parcela, 'pendente', 0)");
            $stmt->bindValue(':emprestimo_id', $emprestimo_id, PDO::PARAM_INT);
            $stmt->bindValue(':numero', $i, PDO::PARAM_INT);
            $stmt->bindValue(':data_vencimento', $data_vencimento, PDO::PARAM_STR);
            $stmt->bindValue(':valor_parcela', $valor_parcela_final, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    /**
     * Método para apagar um empréstimo e suas parcelas
     * @param int $id ID do empréstimo
     * @return bool Resultado da execução da consulta
     */
    public function apagarEmprestimo($id)
    {
        try {
            $this->conn->beginTransaction();
            // Exclui as parcelas associadas ao empréstimo
            $stmt = $this->conn->prepare("DELETE FROM parcelas_do_emprestimo WHERE emprestimo_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            // Exclui o empréstimo
            $stmt = $this->conn->prepare("DELETE FROM emprestimos WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>