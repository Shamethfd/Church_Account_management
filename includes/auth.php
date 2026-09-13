<?php
require_once __DIR__ . '/../config.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}

function attempt_login($usernameOrNumber, $password) {
    global $pdo;
    // 1) Hardcoded admin
    if ($usernameOrNumber === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => 0,
            'name' => 'Administrator',
            'role' => 'admin'
        ];
        return true;
    }

    // 2) App users table (number-based login)
    try {
        $stmt = $pdo->prepare('SELECT id, name, number, service, password FROM app_users WHERE number = ? LIMIT 1');
        $stmt->execute([$usernameOrNumber]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'role' => 'user',
                'service' => $row['service']
            ];
            return true;
        }
    } catch (Throwable $e) {
        // ignore and fall through
    }

    return false;
}

function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'] ?: '/', $params['domain'] ?: '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
