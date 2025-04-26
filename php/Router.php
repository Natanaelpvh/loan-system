<?php

class Router {
    // Método estático para redirecionar para uma URL específica
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
}
?>