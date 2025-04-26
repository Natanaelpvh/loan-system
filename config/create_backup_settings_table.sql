USE clientes_emprestimos;

CREATE TABLE IF NOT EXISTS backup_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intervalo INT NOT NULL,
    last_backup TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
