<?php
session_start();

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function flash($type, $msg) { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

function redirect($url) { header("Location: $url"); exit; }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }

function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Invalid CSRF token.');
    }
}

function db_error_message(Throwable $e) {
    if ($e instanceof mysqli_sql_exception && $e->getCode() === 1062) {
        return 'A record with that email / registration number / ISBN already exists.';
    }
    return $e->getMessage();
}
