<?php
require_once '../php/Backup.php';
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

// Cria uma nova instância da classe Backup
$backup = new Backup();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intervalo = $_POST['intervalo'];
    $backup->salvarConfiguracaoBackup($intervalo);
    $message = $backup->criarBackup();
}

// Obtém a configuração de backup atual
$configuracaoBackup = $backup->obterConfiguracaoBackup();
$intervaloAtual = $configuracaoBackup['intervalo'] ?? null;
$lastBackup = $configuracaoBackup['last_backup'] ?? null;

$title = 'Backup do Banco de Dados';
ob_start();
?>

<main class="container mt-5">
    <h2 class="mb-4">Backup do Banco de Dados</h2>
    <?php if ($message): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form id="backupForm" method="POST" action="backup.php">
        <div class="form-group">
            <label for="intervalo">Escolha o intervalo de backup:</label>
            <div>
                <input type="radio" id="intervalo5" name="intervalo" value="5" <?php echo $intervaloAtual == 5 ? 'checked' : ''; ?>>
                <label for="intervalo5">5 dias</label>
            </div>
            <div>
                <input type="radio" id="intervalo10" name="intervalo" value="10" <?php echo $intervaloAtual == 10 ? 'checked' : ''; ?>>
                <label for="intervalo10">10 dias</label>
            </div>
            <div>
                <input type="radio" id="intervalo15" name="intervalo" value="15" <?php echo $intervaloAtual == 15 ? 'checked' : ''; ?>>
                <label for="intervalo15">15 dias</label>
            </div>
            <div>
                <input type="radio" id="intervalo30" name="intervalo" value="30" <?php echo $intervaloAtual == 30 ? 'checked' : ''; ?>>
                <label for="intervalo30">30 dias</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Salvar Configuração e Criar Backup</button>
    </form>
    <div id="contador" class="mt-4"></div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Obtém o intervalo de backup e a data do último backup do PHP
        var intervalo = <?php echo $intervaloAtual; ?>;
        var lastBackup = new Date("<?php echo $lastBackup; ?>");
        // Calcula a data do próximo backup
        var nextBackup = new Date(lastBackup.getTime() + intervalo * 24 * 60 * 60 * 1000);

        function updateContador() {
            // Obtém a data e hora atual
            var now = new Date();
            // Calcula a diferença de tempo entre o próximo backup e agora
            var timeDiff = nextBackup - now;

            if (timeDiff <= 0) {
                // Se o tempo para o próximo backup já passou, exibe uma mensagem e inicia o backup
                $('#contador').text('Backup em andamento...');
                $.ajax({
                    url: 'backup.php',
                    type: 'POST',
                    data: { intervalo: intervalo },
                    success: function(response) {
                        alert(response); // Exibe a mensagem de sucesso ou erro
                        location.reload(); // Recarrega a página para atualizar o contador
                    },
                    error: function() {
                        alert('Erro ao criar backup.');
                    }
                });
            } else {
                // Calcula os dias, horas, minutos e segundos restantes para o próximo backup
                var days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

                // Atualiza o texto do contador com o tempo restante
                $('#contador').text('Próximo backup em: ' + days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's');
            }
        }

        // Atualiza o contador a cada segundo
        setInterval(updateContador, 1000);
    });
</script>

<?php
$content = ob_get_clean();
include '../includes/template.php';
?>
