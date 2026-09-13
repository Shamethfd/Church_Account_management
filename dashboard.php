<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
global $pdo;
$user = current_user();
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
if (is_admin()) {
  $stm = $pdo->query('SELECT id, month, year, total_collection, bank_deposit, created_at FROM monthly_accounts ORDER BY year DESC, month DESC');
  $rows = $stm->fetchAll();
} else {
  $colCheck = $pdo->query("SHOW COLUMNS FROM monthly_accounts LIKE 'service'");
  $hasService = (bool)$colCheck->fetch();
  if ($hasService) {
    $stm = $pdo->prepare('SELECT id, month, year, total_collection, bank_deposit, created_at FROM monthly_accounts WHERE service = ? ORDER BY year DESC, month DESC');
    $stm->execute([$user['service'] ?? 'Sinhala']);
  } else {
    $stm = $pdo->query('SELECT id, month, year, total_collection, bank_deposit, created_at FROM monthly_accounts ORDER BY year DESC, month DESC');
  }
  $rows = $stm->fetchAll();
}
$accountCount = count($rows);
$totalCollection = 0.0;
$totalDeposit = 0.0;
foreach ($rows as $r) {
  $totalCollection += (float)$r['total_collection'];
  $totalDeposit += (float)$r['bank_deposit'];
}
$latest = $rows[0] ?? null;
$pageTitle = 'Dashboard - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Overview</p>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-lede">Start with members or a monthly account, then print the official report.</p>
      </div>
    </div>

    <section class="start-grid">
      <a class="start-card" href="member_management.php">
        <span>Step 1</span>
        <h3>Families &amp; members</h3>
        <p>Add a family, then add people under that household.</p>
      </a>
      <?php if (is_admin()): ?>
      <a class="start-card" href="account_new.php">
        <span>Step 2</span>
        <h3>New monthly account</h3>
        <p>Enter weekly collections, bank deposit, and signatures.</p>
      </a>
      <a class="start-card" href="admin.php">
        <span>Step 3</span>
        <h3>User access</h3>
        <p>Create member logins for Sinhala or Tamil service.</p>
      </a>
      <?php else: ?>
      <a class="start-card" href="family_new.php">
        <span>Step 2</span>
        <h3>Add family</h3>
        <p>Register a household before adding members.</p>
      </a>
      <a class="start-card" href="member_new.php">
        <span>Step 3</span>
        <h3>Add member</h3>
        <p>Attach a person to an existing family.</p>
      </a>
      <?php endif; ?>
    </section>

    <section class="stats">
      <div class="stat">
        <span>Saved accounts</span>
        <strong><?= $accountCount ?></strong>
      </div>
      <div class="stat">
        <span>Total collection</span>
        <strong>Rs. <?= number_format($totalCollection, 2) ?></strong>
      </div>
      <div class="stat">
        <span>Bank deposits</span>
        <strong>Rs. <?= number_format($totalDeposit, 2) ?></strong>
      </div>
      <div class="stat">
        <span>Latest period</span>
        <strong><?= $latest ? htmlspecialchars(($months[(int)$latest['month']] ?? $latest['month']) . ' ' . $latest['year']) : '—' ?></strong>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Monthly accounts</h2>
          <p class="page-lede">Open a record to view details, edit, or print the official package.</p>
        </div>
      </div>
      <?php if (!$rows): ?>
        <div class="empty-state">No monthly accounts have been saved yet.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Period</th>
              <th class="num">Total collection (Rs.)</th>
              <th class="num">Bank deposit (Rs.)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars(($months[(int)$r['month']] ?? $r['month']) . ' ' . (int)$r['year']) ?></td>
              <td class="num"><?= number_format((float)$r['total_collection'], 2) ?></td>
              <td class="num"><?= number_format((float)$r['bank_deposit'], 2) ?></td>
              <td>
                <div class="btn-row">
                  <a class="btn btn-primary btn-sm" href="account_view.php?id=<?= (int)$r['id'] ?>">View</a>
                  <a class="btn btn-secondary btn-sm" href="report_print.php?id=<?= (int)$r['id'] ?>" target="_blank">Print</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
