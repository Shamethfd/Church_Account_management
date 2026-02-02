<?php
require_once __DIR__ . '/includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!$email || !$password) {
    $error = 'Email and password are required';
  } else if (attempt_login($email, $password)) {
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Invalid credentials';
  }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - CCMC Accounts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container max-w-6xl mx-auto px-4">
    <div class="card max-w-md mx-auto mt-10 rounded-xl border bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold mb-2">Login</h2>
      <?php if ($error): ?><div class="alert error mb-3 rounded-md border text-sm bg-red-50 border-red-200 text-red-800 py-2 px-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-400 focus:ring-slate-400" type="email" name="email" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Password</label>
          <input class="mt-1 w-full rounded-lg border-slate-300 focus:border-slate-400 focus:ring-slate-400" type="password" name="password" required>
        </div>
        <div class="footer-actions flex justify-end">
          <button class="px-4 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 text-sm" type="submit">Sign In</button>
        </div>
      </form>
      <p class="subtitle text-slate-600 text-sm mt-4">Contact the Treasurer to get an account.</p>
    </div>
  </div>
</body>
</html>
