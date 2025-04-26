<?php
require_once '../config/config.php';

class Session
{
    private static $conn; // Conexão com o banco de dados

    /**
     * Método para iniciar a sessão
     * @param PDO $conn Conexão com o banco de dados
     * @throws Exception Se a conexão com o banco de dados não for passada corretamente
     */
    public static function start($conn)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se a conexão foi passada corretamente
        if ($conn === null) {
            throw new Exception("Erro: Conexão com o banco de dados não foi passada corretamente.");
        }

        self::$conn = $conn; // Atribui a conexão recebida à variável estática $conn
    }

    /**
     * Método para salvar a sessão no banco de dados
     * @param string $key Chave da sessão
     * @param mixed $value Valor da sessão
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;

        // Salvar sessão no banco de dados
        if ($key === 'user_id' && !self::exists('session_token')) {
            $session_token = bin2hex(random_bytes(32));  // Gera um token único e seguro
            self::storeSessionInDb($session_token, $value); // Armazena a sessão no banco
        }
    }

    /**
     * Método para verificar se existe uma sessão
     * @param string $key Chave da sessão
     * @return bool True se a sessão existe, False caso contrário
     */
    public static function exists($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Método para armazenar a sessão no banco de dados
     * @param string $session_token Token da sessão
     * @param int $user_id ID do usuário
     */
    private static function storeSessionInDb($session_token, $user_id)
    {
        try {
            // Verifica se já existe um token de sessão para este usuário
            $sql = "SELECT session_token FROM sessions WHERE user_id = :user_id LIMIT 1";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $existingSession = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingSession) {
                // Se já existir um token, apenas atualiza a sessão no banco
                $sql = "UPDATE sessions SET session_token = :session_token, created_at = NOW() WHERE user_id = :user_id";
            } else {
                // Se não existir, insere uma nova sessão
                $sql = "INSERT INTO sessions (session_token, user_id, created_at) VALUES (:session_token, :user_id, NOW())";
            }

            $stmt = self::$conn->prepare($sql);
            $stmt->bindValue(':session_token', $session_token, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Salva o token da sessão na sessão do PHP
            $_SESSION['session_token'] = $session_token;
        } catch (PDOException $e) {
            echo "Erro ao salvar a sessão no banco de dados: " . $e->getMessage();
        }
    }

    /**
     * Método para obter dados da sessão
     * @param string $key Chave da sessão
     * @return mixed Valor da sessão ou null se não existir
     */
    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Método para destruir a sessão
     */
    public static function destroy()
    {
        // Verifica se a sessão já está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            try {
                // Certifique-se de que a conexão com o banco de dados está ativa
                if (!isset(self::$conn)) {
                    throw new Exception("Conexão com o banco de dados não está definida.");
                }

                // Remove a sessão do banco de dados
                $sql = "DELETE FROM sessions WHERE user_id = :user_id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
            } catch (PDOException $e) {
                error_log("Erro ao destruir a sessão no banco de dados: " . $e->getMessage());
            } catch (Exception $e) {
                error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            }
        }

        // Remove todas as variáveis da sessão
        $_SESSION = [];

        // Exclui o cookie de sessão (se existir)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finaliza a sessão
        session_destroy();
    }

    /**
     * Método para autenticar o usuário e definir a função
     * @param int $userId ID do usuário
     */
    public static function authenticate($userId)
    {
        $stmt = self::$conn->prepare("SELECT role FROM users WHERE id = :user_id");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            self::set('user_id', $userId);
            self::set('role', $user['role']);
        }
    }

    /**
     * Método para obter a função do usuário
     * @return string|null Função do usuário ou null se não existir
     */
    public static function getRole()
    {
        return self::get('role');
    }

    /**
     * Método para verificar se o usuário tem uma função específica
     * @param string $role Função a ser verificada
     * @return bool True se o usuário tem a função, False caso contrário
     */
    public static function hasRole($role)
    {
        return self::getRole() === $role;
    }

    /**
     * Método para exigir uma função específica do usuário
     * @param string $role Função exigida
     */
    public static function requireRole($role)
    {
        // Verifica se o usuário tem o papel necessário
        if (!self::hasRole($role)) {
            // Redireciona para a página de 'unauthorized.php'
            header("Location: /loan-system/system/unauthorized.php");
            exit();
        }
    }

}
?>