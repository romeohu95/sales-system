<?php
include '../config/settings.php';

if (isset($_SESSION['user_id'])) {
    if ((time() - $_SESSION['last_activity']) > $tiempo_sesion) {
        // Sesión expirada
        session_unset();
        session_destroy();
        header("Location: index.php?url-sis=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    } else {
        $_SESSION['last_activity'] = time();
    }
}
?>