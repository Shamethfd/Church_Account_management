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
  <title>View Monthly Account - CCMC Accounts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container max-w-6xl mx-auto px-4">
    <div class="card rounded-xl border bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold mb-2">Monthly Account: <?= htmlspecialchars($acc['month']) ?>/<?= htmlspecialchars($acc['year']) ?></h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
        <p><strong>Service Day:</strong> <?= htmlspecialchars($acc['service_day']) ?></p>
        <p><strong>Total Collection:</strong> Rs. <?= number_format((float)$acc['total_collection'], 2) ?></p>
        <p><strong>Bank Deposit:</strong> Rs. <?= number_format((float)$acc['bank_deposit'], 2) ?></p>
        <p><strong>Bank Slip No:</strong> <?= htmlspecialchars($acc['bank_slip_no'] ?? '') ?></p>
      </div>

      <h3 class="mt-6 font-semibold">Weekly Collections</h3>
      <table class="table w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-slate-700">
            <th class="px-2 py-1 text-left">Week</th>
            <th class="px-2 py-1 text-left">Date</th>
            <th class="px-2 py-1 text-left">Collection</th>
            <th class="px-2 py-1 text-left">Other</th>
            <th class="px-2 py-1 text-left">Thanks</th>
            <th class="px-2 py-1 text-left">Christian Service</th>
            <th class="px-2 py-1 text-left">Monthly</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($weeks as $w): ?>
            <tr class="border-b last:border-0">
              <td class="px-2 py-1"><?= (int)$w['week'] ?></td>
              <td class="px-2 py-1"><?= htmlspecialchars($w['service_date'] ?? '') ?></td>
              <td class="px-2 py-1"><?= number_format((float)$w['collection_amount'], 2) ?></td>
              <td class="px-2 py-1"><?= number_format((float)$w['other_offerings'], 2) ?></td>
              <td class="px-2 py-1"><?= number_format((float)$w['thanks_offering'], 2) ?></td>
              <td class="px-2 py-1"><?= number_format((float)$w['christian_service'], 2) ?></td>
              <td class="px-2 py-1"><?= number_format((float)$w['monthly_offering'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="footer-actions flex justify-end gap-2 mt-4">
        <a class="btn px-4 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 text-sm" href="report_print.php?id=<?= (int)$acc['id'] ?>" target="_blank">Open Print View</a>
        <a class="btn secondary px-4 py-2 rounded-md border text-slate-700 hover:bg-slate-50 text-sm" href="dashboard.php">Back</a>
      </div>
    </div>
  </div>
</body>
</html>
