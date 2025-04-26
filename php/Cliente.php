<?php
require_once '../config/config.php';

class Cliente
{
    private $conn;

    /**
     * Construtor da classe Cliente
     * Instancia a classe Config e obtém a conexão com o banco de dados
     */
    public function __construct()
    {
        $config = new Config();
        $this->conn = $config->connect();
    }

    /**
     * Método para listar todos os clientes
     * @return array Lista de clientes
     */
    public function listarClientes()
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes ORDER BY nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para listar os clientes filtrados com paginação
     * @param string $nome Nome do cliente
     * @param string $cpf CPF do cliente
     * @param string $status_parcela Status da parcela
     * @param int $offset Offset para a consulta SQL
     * @param int $limit Limite de resultados por página
     * @return array Lista de clientes filtrados
     */
    public function listarClientesFiltrados($nome, $cpf, $status_parcela, $offset, $limit)
    {
        $sql = "SELECT DISTINCT c.id, c.nome, c.cpf, c.status 
                FROM clientes c 
                LEFT JOIN emprestimos e ON c.id = e.cliente_id 
                LEFT JOIN parcelas_do_emprestimo p ON e.id = p.emprestimo_id 
                WHERE 1=1";
        $params = [];

        if (!empty($nome)) {
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $nome . '%';
        }

        if (!empty($cpf)) {
            $sql .= " AND c.cpf LIKE :cpf";
            $params[':cpf'] = '%' . $cpf . '%';
        }

        if (!empty($status_parcela)) {
            $sql .= " AND p.status = :status_parcela";
            $params[':status_parcela'] = $status_parcela;
        }

        $sql .= " ORDER BY c.id DESC";
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Método para contar o total de clientes filtrados
     * @param string $nome Nome do cliente
     * @param string $cpf CPF do cliente
     * @param string $status_parcela Status da parcela
     * @return int Total de clientes filtrados
     */
    public function contarClientesFiltrados($nome, $cpf, $status_parcela)
    {
        $sql = "SELECT COUNT(DISTINCT c.id) AS total 
                FROM clientes c 
                LEFT JOIN emprestimos e ON c.id = e.cliente_id 
                LEFT JOIN parcelas_do_emprestimo p ON e.id = p.emprestimo_id 
                WHERE 1=1";
        $params = [];

        if (!empty($nome)) {
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $nome . '%';
        }

        if (!empty($cpf)) {
            $sql .= " AND c.cpf LIKE :cpf";
            $params[':cpf'] = '%' . $cpf . '%';
        }

        if (!empty($status_parcela)) {
            $sql .= " AND p.status = :status_parcela";
            $params[':status_parcela'] = $status_parcela;
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Método para adicionar um novo cliente
     * @param string $nome Nome do cliente
     * @param string $cpf CPF do cliente
     * @param string $email Email do cliente
     * @param string $telefone Telefone do cliente
     * @param string $rua Rua do cliente
     * @param string $numero Número da residência do cliente
     * @param string $bairro Bairro do cliente
     * @param string $cidade Cidade do cliente
     * @param string $estado Estado do cliente
     * @param string $cep CEP do cliente
     * @param string $status Status do cliente
     * @return string Mensagem de sucesso ou erro
     */
    public function adicionarCliente($nome, $cpf, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $status)
    {
        $stmt = $this->conn->prepare("INSERT INTO clientes (nome, cpf, email, telefone, rua, numero, bairro, cidade, estado, cep, status) VALUES (:nome, :cpf, :email, :telefone, :rua, :numero, :bairro, :cidade, :estado, :cep, :status)");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':cpf', $cpf);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telefone', $telefone);
        $stmt->bindValue(':rua', $rua);
        $stmt->bindValue(':numero', $numero);
        $stmt->bindValue(':bairro', $bairro);
        $stmt->bindValue(':cidade', $cidade);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':cep', $cep);
        $stmt->bindValue(':status', $status);

        if ($stmt->execute()) {
            return "Cliente adicionado com sucesso!";
        } else {
            return "Erro ao adicionar cliente: " . $stmt->errorInfo()[2];
        }
    }

    /**
     * Método para obter as informações de um cliente específico
     * @param int $id ID do cliente
     * @return array Informações do cliente
     */
    public function obterCliente($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para atualizar as informações de um cliente
     * @param int $id ID do cliente
     * @param string $nome Nome do cliente
     * @param string $cpf CPF do cliente
     * @param string $email Email do cliente
     * @param string $telefone Telefone do cliente
     * @param string $rua Rua do cliente
     * @param string $numero Número da residência do cliente
     * @param string $bairro Bairro do cliente
     * @param string $cidade Cidade do cliente
     * @param string $estado Estado do cliente
     * @param string $cep CEP do cliente
     * @param string $status Status do cliente
     * @return bool
     */
    public function atualizarCliente($id, $nome, $cpf, $email, $telefone, $rua, $numero, $bairro, $cidade, $estado, $cep, $status)
    {
        try {
            $sql = "UPDATE clientes SET nome = :nome, cpf = :cpf, email = :email, telefone = :telefone, rua = :rua, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado, cep = :cep, status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindValue(':rua', $rua, PDO::PARAM_STR);
            $stmt->bindValue(':numero', $numero, PDO::PARAM_STR);
            $stmt->bindValue(':bairro', $bairro, PDO::PARAM_STR);
            $stmt->bindValue(':cidade', $cidade, PDO::PARAM_STR);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':cep', $cep, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para ver os detalhes do cliente
     * @param int $id ID do cliente
     * @return array|null Detalhes do cliente ou null se não encontrado
     */
    public function VerDetalheCliente($id)
    {
        $stmt = $this->conn->prepare("SELECT nome, cpf, email, telefone, rua, numero, bairro, cidade, estado, cep, status FROM clientes WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        return $cliente ?: null;
    }

    /**
     * Método para listar os clientes com parcelas vencidas
     * @param int $offset Offset para a consulta SQL
     * @param int $limit Limite de resultados por página
     * @return array Lista de clientes com parcelas vencidas
     */
    public function listarClientesComParcelasVencidas($offset, $limit)
    {
        $dataAtual = date('Y-m-d');

        $sql = "SELECT DISTINCT c.id, c.nome, c.email, p.data_vencimento, p.status
            FROM clientes c
            JOIN emprestimos e ON c.id = e.cliente_id
            JOIN parcelas_do_emprestimo p ON e.id = p.emprestimo_id
            WHERE p.data_vencimento < :data_atual 
            AND p.status = 'ATRASADO'
            ORDER BY p.data_vencimento ASC
            LIMIT :limit OFFSET :offset";

        $params = [
            ':data_atual' => $dataAtual,
            ':limit' => $limit,
            ':offset' => $offset,
        ];

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function contarClientesComParcelasVencidas()
    {
        $dataAtual = date('Y-m-d');

        $stmt = $this->conn->prepare("SELECT COUNT(DISTINCT c.id) AS total
            FROM clientes c
            JOIN emprestimos e ON c.id = e.cliente_id
            JOIN parcelas_do_emprestimo p ON e.id = p.emprestimo_id
            WHERE p.data_vencimento < :data_atual 
            AND p.status = 'ATRASADO'");

        $stmt->bindValue(':data_atual', $dataAtual);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Método para apagar um cliente
     * @param int $id ID do cliente
     * @return string Mensagem de sucesso ou erro
     */
    public function apagarCliente($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id = :id");
        $stmt->bindValue(':id', $id);

        if ($stmt->execute()) {
            return "Cliente apagado com sucesso!";
        } else {
            return "Erro ao apagar cliente: " . $stmt->errorInfo()[2];
        }
    }
    /**
     * Summary of contarClientes
     */
    public function contarClientes()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM clientes");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] ?? 0;
    }

}
?>