<?php

// Caminho base do projeto
define('BASE_URL', '/loan-system/');  // Define o caminho base do sistema, utilizado para URLs

/**
 * Classe responsável pela configuração e conexão com o banco de dados MySQL.
 */
class Config
{
    /**
     * @var string Host do banco de dados (servidor onde o MySQL está hospedado).
     */
    private $host = 'localhost';  // O endereço do servidor do banco de dados.

    /**
     * @var string Nome do banco de dados ao qual a aplicação se conecta.
     */
    private $db_name = 'clientes_emprestimos';  // Nome do banco de dados.

    /**
     * @var string Nome de usuário para autenticação no banco de dados.
     */
    private $username = 'root';  // Nome de usuário para se conectar ao banco de dados.

    /**
     * @var string Senha para autenticação no banco de dados.
     */
    private $password = '';  // Senha para autenticação no banco de dados.

    /**
     * @var PDO Armazena a instância da conexão com o banco de dados.
     */
    public $conn;  // A variável que manterá a conexão ativa com o banco de dados.

    /**
     * Método responsável por estabelecer a conexão com o banco de dados.
     *
     * Tenta criar uma instância PDO utilizando as credenciais fornecidas e,
     * caso a conexão seja bem-sucedida, armazena a instância no atributo $conn.
     * Se ocorrer algum erro, uma exceção será lançada.
     *
     * @return PDO|null Retorna a instância PDO de conexão ou null caso ocorra um erro.
     */
    public function connect()
    {
        // Inicializa a variável de conexão como null
        $this->conn = null;

        try {
            // Tenta criar a instância PDO para se conectar ao banco de dados
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Define o modo de erro como exceção, para capturar falhas de conexão
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Removido a configuração de fuso horário, agora o banco de dados usará a configuração padrão

        } catch (PDOException $exception) {
            // Exibe uma mensagem de erro em caso de falha na conexão
            echo "Connection error: " . $exception->getMessage();
        }

        // Retorna a instância de conexão com o banco de dados ou null se falhar
        return $this->conn;
    }

    /**
     * Método para testar a conexão com o banco de dados.
     *
     * Tenta criar uma instância PDO e testar a conexão utilizando as credenciais fornecidas.
     * Caso a conexão seja bem-sucedida, exibe uma mensagem de sucesso. Caso contrário,
     * exibe uma mensagem de erro.
     *
     * @return void
     */
    public function testConnection()
    {
        try {
            // Tenta estabelecer uma conexão com o banco de dados para testar
            $conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Configura o modo de erro para lançar exceções
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Exibe uma mensagem de sucesso se a conexão for bem-sucedida
            echo "Conexão com o banco de dados bem-sucedida!";
        } catch (PDOException $exception) {
            // Exibe uma mensagem de erro em caso de falha na conexão
            echo "Erro de conexão: " . $exception->getMessage();
        }
    }
}
?>
