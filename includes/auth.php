<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['user_type'] != 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function require_employee() {
    require_login();
    if ($_SESSION['user_type'] != 'employee') {
        header("Location: ../index.php");
        exit();
    }
}

function require_passenger() {
    require_login();
    if ($_SESSION['user_type'] != 'passenger') {
        header("Location: ../index.php");
        exit();
    }
}
?>