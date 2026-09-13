<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$error_message = $_SESSION['error_message'] ?? 'An error occurred.';
$error_details = $_SESSION['error_details'] ?? 'Please try again or contact support.';

unset($_SESSION['error_message']);
unset($_SESSION['error_details']);
$pageTitle = 'Error - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <section class="panel" style="max-width:680px;margin:40px auto;text-align:center">
      <p class="page-kicker">System notice</p>
      <h1 class="page-title"><?= htmlspecialchars($error_message) ?></h1>
      <p class="page-lede" style="margin:0 auto"><?= htmlspecialchars($error_details) ?></p>
      <div class="btn-row" style="justify-content:center;margin-top:22px">
        <a href="javascript:history.back()" class="btn btn-secondary">Go back</a>
        <a href="account_new.php" class="btn btn-primary">New account</a>
        <a href="db_check.php" class="btn btn-secondary">Check database</a>
        <a href="dashboard.php" class="btn btn-gold">Dashboard</a>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
