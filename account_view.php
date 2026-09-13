<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

global $pdo;
$id = (int)($_GET['id'] ?? 0);
$stm = $pdo->prepare('SELECT * FROM monthly_accounts WHERE id = ?');
$stm->execute([$id]);
$acc = $stm->fetch();
if (!$acc) { http_response_code(404); echo 'Not found'; exit; }

$user = current_user();
if (!is_admin()) {
  if (array_key_exists('service', $acc) && isset($user['service']) && $acc['service'] !== $user['service']) {
    http_response_code(403);
    echo 'Access denied';
    exit;
  }
}

$wstm = $pdo->prepare('SELECT * FROM weekly_collections WHERE account_id = ? ORDER BY week ASC');
$wstm->execute([$id]);
$weeks = $wstm->fetchAll();
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
$period = ($months[(int)$acc['month']] ?? $acc['month']) . ' ' . $acc['year'];
$pageTitle = 'View Monthly Account - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Accounts</p>
        <h1 class="page-title"><?= htmlspecialchars($period) ?></h1>
        <p class="page-lede">Read-only summary of collections, banking, and weekly entries.</p>
      </div>
      <div class="page-actions">
        <?php if (is_admin()): ?>
          <a class="btn btn-gold" href="account_edit.php?id=<?= (int)$acc['id'] ?>">Edit</a>
        <?php endif; ?>
        <a class="btn btn-primary" href="report_print.php?id=<?= (int)$acc['id'] ?>" target="_blank">Print view</a>
        <a class="btn btn-secondary" href="dashboard.php">Back</a>
      </div>
    </div>

    <section class="panel">
      <div class="meta-grid">
        <div class="meta-item"><span>Service day</span><strong><?= htmlspecialchars($acc['service_day']) ?></strong></div>
        <?php if (array_key_exists('service', $acc)): ?>
        <div class="meta-item"><span>Service</span><strong><?= htmlspecialchars($acc['service']) ?></strong></div>
        <?php endif; ?>
        <div class="meta-item"><span>Total collection</span><strong>Rs. <?= number_format((float)$acc['total_collection'], 2) ?></strong></div>
        <div class="meta-item"><span>Bank deposit</span><strong>Rs. <?= number_format((float)$acc['bank_deposit'], 2) ?></strong></div>
        <div class="meta-item"><span>Bank slip no</span><strong><?= htmlspecialchars($acc['bank_slip_no'] ?? '—') ?></strong></div>
      </div>
    </section>

    <section class="panel">
      <h2>Weekly collections</h2>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Week</th>
              <th>Date</th>
              <th class="num">Collection</th>
              <th class="num">Other</th>
              <th class="num">Thanks</th>
              <th class="num">Christian service</th>
              <th class="num">Monthly</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($weeks as $w): ?>
              <tr>
                <td class="week-label"><?= (int)$w['week'] ?></td>
                <td><?= htmlspecialchars($w['service_date'] ?? '') ?></td>
                <td class="num"><?= number_format((float)$w['collection_amount'], 2) ?></td>
                <td class="num"><?= number_format((float)$w['other_offerings'], 2) ?></td>
                <td class="num"><?= number_format((float)$w['thanks_offering'], 2) ?></td>
                <td class="num"><?= number_format((float)$w['christian_service'], 2) ?></td>
                <td class="num"><?= number_format((float)$w['monthly_offering'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
