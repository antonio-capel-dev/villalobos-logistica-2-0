<?php
// backend/auth_guard.php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function tieneRol($rolRequerido) {
    if (!isset($_SESSION['rol'])) return false;
    return $_SESSION['rol'] === $rolRequerido;
}
?>
