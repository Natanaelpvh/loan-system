<?php

require_once '../config/config.php'; // Inclui a conexão com o banco de dados

/**
 * Classe User
 * Responsável por gerenciar operações de usuário como: registro, atualização, exclusão, login e listagem
 */
class User
{
    private $conn; // Conexão com o banco de dados

    /**
     * Construtor da classe User
     * Instancia a classe Config e obtém a conexão com o banco de dados
     */
    public function __construct()
    {
        $config = new Config();
        $this->conn = $config->connect();

    }


    /**
     * Registra um novo usuário no banco de dados
     * 
     * @param string $username Nome de usuário
     * @param string $email Email do usuário
     * @param string $password Senha do usuário
     * @return string Mensagem de sucesso ou erro
     */
    public function register($username, $email, $password)
    {
        // Verifica se o e-mail já está cadastrado
        $check_sql = "SELECT id FROM users WHERE email = :email";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return "E-mail já cadastrado!";
        }

        // Criptografa a senha antes de armazenar
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // SQL para inserir o novo usuário no banco de dados
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        // Verifica se o usuário foi registrado com sucesso
        return $stmt->execute() ? "Registro realizado com sucesso!" : "Erro ao registrar usuário.";
    }

    /**
     * Atualiza os dados de um usuário no banco de dados
     * 
     * @param int $user_id ID do usuário a ser atualizado
     * @param string $username Novo nome de usuário
     * @param string $email Novo email do usuário
     * @param string|null $password Nova senha do usuário (opcional)
     * @return mixed Retorna true se a atualização for bem-sucedida ou mensagem de erro
     */
    public function update($user_id, $username, $email, $role = null, $password = null)
    {
        if (!$user_id) {
            return "Usuário não identificado!";
        }

        // SQL para atualizar os dados do usuário
        $sql = "UPDATE users SET username = :username, email = :email";

        // Se a senha for fornecida, criptografa e inclui na atualização
        if (!empty($password)) {
            $sql .= ", password = :password";
        }

        // Se o role for fornecido, inclui na atualização
        if (!is_null($role)) {
            $sql .= ", role = :role";
        }

        $sql .= " WHERE id = :id";

        // Prepara e executa a consulta SQL
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $user_id);

        // Se a senha for fornecida, criptografa a senha
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }

        // Se o role for fornecido, atualiza o campo role
        if (!is_null($role)) {
            $stmt->bindParam(':role', $role);
        }

        // Verifica se a atualização foi bem-sucedida
        return $stmt->execute() ? true : "Erro ao atualizar os dados do usuário.";
    }


    /**
     * Deleta um usuário do banco de dados
     * 
     * @param int $user_id ID do usuário a ser deletado
     * @return bool Retorna true se a exclusão for bem-sucedida, caso contrário, false
     */
    public function delete($user_id)
    {
        // SQL para deletar o usuário do banco de dados
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $user_id);

        // Verifica se a exclusão foi bem-sucedida
        return $stmt->execute();
    }

    /**
     * Realiza o login do usuário
     * 
     * @param string $username Nome de usuário
     * @param string $password Senha do usuário
     * @return bool Retorna true se o login for bem-sucedido, caso contrário, false
     */
    public function login($username, $password)
    {
        // SQL para buscar o usuário pelo nome de usuário
        $sql = "SELECT id, username, password FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Verifica se o usuário foi encontrado
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verifica a senha informada
            if (password_verify($password, $row['password'])) {
                // Inicia a sessão e armazena os dados do usuário
                Session::start();
                Session::set('user_id', $row['id']);
                Session::set('username', $row['username']);
                return true; // Login bem-sucedido
            }
        }
        return false; // Credenciais inválidas
    }

    /**
     * Lista todos os usuários cadastrados no banco de dados
     * 
     * @return array|mixed Retorna um array com os usuários ou uma mensagem caso não haja registros
     */
    public function listUsers()
    {
        try {
            // SQL para selecionar todos os usuários
            $sql = "SELECT id, username, email FROM users ORDER BY username ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            // Retorna todos os usuários encontrados
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Se não houver usuários, retorna uma mensagem
            if (empty($users)) {
                return "Nenhum usuário cadastrado.";
            }

            return $users;
        } catch (PDOException $e) {
            // Em caso de erro, exibe uma mensagem
            return "Erro ao listar usuários: " . $e->getMessage();
        }
    }

    /**
     * Método para verificar se um usuário está logado
     * @return bool True se o usuário está logado, False caso contrário
     */
    public function isLoggedIn()
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    /**
     * Método para fazer logout de um usuário
     */
    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
    }

    /**
     * Método para obter as informações de um usuário específico
     * @param int $id ID do usuário
     * @return array Informações do usuário
     */
    public function getUser($id)
    {
        // Prepara a instrução SQL para buscar as informações do usuário
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}

?>