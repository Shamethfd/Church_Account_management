<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

global $pdo;
$id = (int)($_GET['id'] ?? 0);
$stm = $pdo->prepare('SELECT * FROM monthly_accounts WHERE id = ?');
$stm->execute([$id]);
$acc = $stm->fetch();
if (!$acc) { http_response_code(404); echo 'Not found'; exit; }
$wstm = $pdo->prepare('SELECT * FROM weekly_collections WHERE account_id = ? ORDER BY week ASC');
$wstm->execute([$id]);
$weeks = $wstm->fetchAll();
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Monthly Account Report</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .report h2 { margin: 0; text-align:center; }
    .report h3 { margin: 8px 0; text-align:center; font-weight: 500; }
    .signatures { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-top: 16px; }
    .signatures div { border-top: 1px solid #94a3b8; padding-top: 6px; }
  </style>
</head>
<body>
  <div class="print-page">
    <div class="report">
      <h2>COLOMBO CITY MISSION CIRCUIT</h2>
      <h3>Monthly Account Package</h3>
      <p><strong>Month:</strong> <?= htmlspecialchars($acc['month']) ?>, <strong>Year:</strong> <?= htmlspecialchars($acc['year']) ?>, <strong>Day of Service:</strong> <?= htmlspecialchars($acc['service_day']) ?></p>

      <table class="table">
        <thead>
          <tr>
            <th>Week</th>
            <th>Date</th>
            <th>Collection Amount (Rs.)</th>
            <th>Other Offerings</th>
            <th>Thanks Offering</th>
            <th>Christian Service</th>
            <th>Monthly Offering</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($weeks as $w): ?>
          <tr>
            <td><?= (int)$w['week'] ?></td>
            <td><?= htmlspecialchars($w['service_date'] ?? '') ?></td>
            <td><?= number_format((float)$w['collection_amount'], 2) ?></td>
            <td><?= number_format((float)$w['other_offerings'], 2) ?></td>
            <td><?= number_format((float)$w['thanks_offering'], 2) ?></td>
            <td><?= number_format((float)$w['christian_service'], 2) ?></td>
            <td><?= number_format((float)$w['monthly_offering'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Summary</h3>
      <p><strong>Total Collection:</strong> Rs. <?= number_format((float)$acc['total_collection'], 2) ?></p>
      <p><strong>Bank Deposit:</strong> Rs. <?= number_format((float)$acc['bank_deposit'], 2) ?></p>
      <p><strong>Bank Slip Ref:</strong> <?= htmlspecialchars($acc['bank_slip_no'] ?? '') ?></p>

      <h3>Certification & Signatures</h3>
      <div class="signatures">
        <div>
          <div><strong>Prepared by (Treasurer):</strong> <?= htmlspecialchars($acc['prepared_by'] ?? '') ?></div>
          <div>Date: <?= htmlspecialchars($acc['prepared_date'] ?? '') ?></div>
        </div>
        <div>
          <div><strong>Verified by (Circuit Steward):</strong> <?= htmlspecialchars($acc['verified_by'] ?? '') ?></div>
          <div>Date: <?= htmlspecialchars($acc['verified_date'] ?? '') ?></div>
        </div>
        <div>
          <div><strong>Approved by:</strong> <?= htmlspecialchars($acc['approved_name'] ?? '') ?></div>
          <div>Date: <?= htmlspecialchars($acc['approved_date'] ?? '') ?></div>
        </div>
      </div>

      <div style="margin-top:16px;">
        <strong>Church Seal:</strong>
        <div class="seal-box"></div>
      </div>

      <div class="no-print" style="margin-top:16px; display:flex; gap:8px;">
        <button onclick="window.print()">Print</button>
        <a class="btn secondary" href="account_view.php?id=<?= (int)$acc['id'] ?>">Back</a>
      </div>
    </div>
  </div>
</body>
</html>
