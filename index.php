<?php
// Inclui o arquivo Router.php para gerenciar redirecionamentos
require_once 'php/Router.php';

// Redireciona diretamente para o dashboard
Router::redirect('system/dashboard.php');
?>