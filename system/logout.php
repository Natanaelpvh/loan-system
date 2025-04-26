<?php
include('../php/Session.php'); // Inclui a classe de sessão

// Encerra a sessão do usuário
Session::destroy();
header("Location: login.php"); // Redireciona para o login após logout
exit();
?>
