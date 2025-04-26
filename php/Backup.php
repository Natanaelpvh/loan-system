<?php
require_once '../config/config.php';

class Backup
{
    private $conn;

    /**
     * Construtor da classe Backup
     * Instancia a classe Config e obtém a conexão com o banco de dados
     */
    public function __construct()
    {
        $config = new Config();
        $this->conn = $config->connect();
    }

    /**
     * Método para criar um backup do banco de dados
     * @return string Mensagem de sucesso ou erro
     */
    public function criarBackup()
    {
        try {
            $backupDir = 'c:/bakup_banco_sis';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }

            $backupFile = $backupDir . '/backup_' . date('Y-m-d_H-i-s') . '.txt';
            $tables = $this->conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

            $backupContent = '';
            foreach ($tables as $table) {
                $createTableStmt = $this->conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
                if (isset($createTableStmt['Create Table'])) {
                    $backupContent .= $createTableStmt['Create Table'] . ";\n\n";
                }

                $rows = $this->conn->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $values = array_map([$this->conn, 'quote'], array_values($row));
                    $backupContent .= "INSERT INTO $table VALUES (" . implode(', ', $values) . ");\n";
                }
                $backupContent .= "\n\n";
            }

            file_put_contents($backupFile, $backupContent);
            $this->atualizarUltimoBackup();
            return "Backup criado com sucesso em $backupFile";
        } catch (Exception $e) {
            return "Erro ao criar backup: " . $e->getMessage();
        }
    }

    /**
     * Método para salvar a configuração de backup
     * @param int $intervalo Intervalo de backup em dias
     */
    public function salvarConfiguracaoBackup($intervalo)
    {
        // Apaga a configuração existente
        $stmt = $this->conn->prepare("DELETE FROM backup_settings");
        $stmt->execute();

        // Insere a nova configuração
        $stmt = $this->conn->prepare("INSERT INTO backup_settings (intervalo) VALUES (:intervalo)");
        $stmt->bindValue(':intervalo', $intervalo, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Método para obter a configuração de backup
     * @return array Configuração de backup
     */
    public function obterConfiguracaoBackup()
    {
        $stmt = $this->conn->prepare("SELECT intervalo, last_backup FROM backup_settings LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Método para atualizar a data do último backup
     */
    private function atualizarUltimoBackup()
    {
        $stmt = $this->conn->prepare("UPDATE backup_settings SET last_backup = CURRENT_TIMESTAMP");
        $stmt->execute();
    }
}
?>
