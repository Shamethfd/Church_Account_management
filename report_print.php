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
$pageTitle = 'Monthly Account Report';
$includeAppJs = false;
require __DIR__ . '/includes/head.php';
?>
<body>
  <div class="print-page">
    <div class="report">
      <p class="page-kicker" style="text-align:center">Official record</p>
      <h1 class="page-title" style="text-align:center;margin-bottom:4px">Colombo City Mission Circuit</h1>
      <h2 style="text-align:center;margin:0 0 16px;font-size:22px">Monthly Account Package — <?= htmlspecialchars($period) ?></h2>
      <div class="meta-grid" style="margin-bottom:18px">
        <div class="meta-item"><span>Day of service</span><strong><?= htmlspecialchars($acc['service_day']) ?></strong></div>
        <?php if (array_key_exists('service', $acc)): ?>
        <div class="meta-item"><span>Service</span><strong><?= htmlspecialchars($acc['service']) ?></strong></div>
        <?php endif; ?>
        <div class="meta-item"><span>Year</span><strong><?= htmlspecialchars((string)$acc['year']) ?></strong></div>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Week</th>
            <th>Date</th>
            <th class="num">Collection Amount (Rs.)</th>
            <th class="num">Other Offerings</th>
            <th class="num">Thanks Offering</th>
            <th class="num">Christian Service</th>
            <th class="num">Monthly Offering</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($weeks as $w): ?>
          <tr>
            <td><?= (int)$w['week'] ?></td>
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

      <h3 style="margin-top:22px">Summary</h3>
      <div class="meta-grid">
        <div class="meta-item"><span>Total collection</span><strong>Rs. <?= number_format((float)$acc['total_collection'], 2) ?></strong></div>
        <div class="meta-item"><span>Bank deposit</span><strong>Rs. <?= number_format((float)$acc['bank_deposit'], 2) ?></strong></div>
        <div class="meta-item"><span>Bank slip ref</span><strong><?= htmlspecialchars($acc['bank_slip_no'] ?? '') ?></strong></div>
      </div>

      <h3 style="margin-top:22px">Certification &amp; signatures</h3>
      <div class="meta-grid">
        <div class="meta-item">
          <span>Prepared by (Treasurer)</span>
          <strong><?= htmlspecialchars($acc['prepared_by'] ?? '') ?></strong>
          <p class="hint">Date: <?= htmlspecialchars($acc['prepared_date'] ?? '') ?></p>
        </div>
        <div class="meta-item">
          <span>Verified by (Circuit Steward)</span>
          <strong><?= htmlspecialchars($acc['verified_by'] ?? '') ?></strong>
          <p class="hint">Date: <?= htmlspecialchars($acc['verified_date'] ?? '') ?></p>
        </div>
        <div class="meta-item">
          <span>Approved by</span>
          <strong><?= htmlspecialchars($acc['approved_name'] ?? '') ?></strong>
          <p class="hint">Date: <?= htmlspecialchars($acc['approved_date'] ?? '') ?></p>
        </div>
      </div>

      <div style="margin-top:20px;">
        <strong>Church seal</strong>
        <div class="seal-box" style="margin-top:8px">Official seal</div>
      </div>

      <div class="no-print form-actions" style="justify-content:flex-start">
        <button class="btn btn-primary" type="button" onclick="window.print()">Print</button>
        <a class="btn btn-secondary" href="account_view.php?id=<?= (int)$acc['id'] ?>">Back</a>
      </div>
    </div>
  </div>
<?php require __DIR__ . '/includes/footer.php'; ?>
