<?php
require_once __DIR__ . '/includes/auth.php';

$alreadyIn = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($alreadyIn) {
    header('Location: dashboard.php');
    exit;
  }
  $number = trim($_POST['number'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($number === '' || $password === '') {
    $error = 'Enter your number and password.';
  } else if (attempt_login($number, $password)) {
    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Those details are not correct. Try again.';
  }
}

$pageTitle = 'Sign in - CCMC Accounts';
$includeAppJs = false;
require __DIR__ . '/includes/head.php';
?>
<body class="auth-body">
  <main class="auth">
    <section class="auth-side">
      <div>
        <div class="brand-mark">CC</div>
        <p class="page-kicker">Colombo City Mission Circuit</p>
        <h1>Monthly Account Management</h1>
        <p>Sign in to record collections, print monthly packages, and keep family records in one place.</p>
      </div>
    </section>
    <section class="auth-main">
      <div class="auth-card">
        <h2>Sign in</h2>
        <p class="page-lede">Use your admin username or member number.</p>

        <?php if ($alreadyIn && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
          <div class="alert success">
            You are already signed in as <strong><?= htmlspecialchars($alreadyIn['name'] ?? 'User') ?></strong>.
          </div>
          <div class="form-actions" style="justify-content:flex-start">
            <a class="btn btn-primary" href="dashboard.php">Continue to dashboard</a>
            <a class="btn btn-secondary" href="logout.php">Sign out</a>
          </div>
        <?php else: ?>
          <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="post" action="login.php" autocomplete="off">
            <div class="field">
              <label for="number">Number / username</label>
              <input id="number" type="text" name="number" required autofocus>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input id="password" type="password" name="password" required>
            </div>
            <div class="form-actions" style="justify-content:flex-start">
              <button class="btn btn-primary" type="submit">Sign in</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
