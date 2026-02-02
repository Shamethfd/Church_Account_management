<?php
// One-time admin creation script. Only works if there are no users yet.
require_once __DIR__ . '/config.php';

// Check if users already exist
try {
    $cnt = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Run db.sql (and alter.sql) to create tables before seeding admin.';
    exit;
}

if ($cnt > 0) {
    http_response_code(403);
    echo 'Admin seeding is disabled because users already exist.';
    exit;
}

// Simple CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$name || !$email || !$password) {
            $errors[] = 'All fields are required.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stm = $pdo->prepare('INSERT INTO users (name, email, role, password) VALUES (?,?,"admin",?)');
            try {
                $stm->execute([$name, $email, $hash]);
                $success = 'Admin user created. You can now delete seed_admin.php and login.';
            } catch (Throwable $e) {
                $errors[] = 'Failed to create admin. Email may already exist.';
            }
        }
    }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seed Admin - CCMC Accounts</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <div class="container" style="max-width:600px; margin:40px auto;">
    <div class="card">
      <h2>Create Admin User</h2>
      <p class="subtitle">This page works only when no users exist. Remove this file after use.</p>
      <?php foreach ($errors as $e): ?><div class="alert error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <label>Name</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <div class="footer-actions">
          <button type="submit">Create Admin</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
