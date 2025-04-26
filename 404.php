<?php
http_response_code(404);
$title = "Página Não Encontrada";
ob_start();
?>

<div class="container text-center mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h1 class="display-1 text-danger">404</h1>
                    <h2 class="text-secondary">Oops! Página Não Encontrada</h2>
                    <p class="lead text-muted">
                        A página que você está procurando não existe ou foi movida.
                    </p>
                    <a href="/loan-system/index.php" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-home"></i> Voltar para a Página Inicial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/template.php';
?>

