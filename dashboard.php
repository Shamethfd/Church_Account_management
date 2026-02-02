<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
global $pdo;
$stm = $pdo->query('SELECT id, month, year, total_collection, bank_deposit, created_at FROM monthly_accounts ORDER BY year DESC, month DESC');
$rows = $stm->fetchAll();
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - CCMC Accounts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container max-w-6xl mx-auto px-4">
    <div class="card rounded-xl border bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold mb-4">Saved Monthly Accounts</h2>
      <div class="overflow-x-auto">
        <table class="table w-full text-sm">
          <thead>
            <tr class="bg-slate-100 text-slate-700">
              <th class="px-3 py-2 text-left">Month</th>
              <th class="px-3 py-2 text-left">Year</th>
              <th class="px-3 py-2 text-left">Total Collection (Rs.)</th>
              <th class="px-3 py-2 text-left">Bank Deposit (Rs.)</th>
              <th class="px-3 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr class="border-b last:border-0">
              <td class="px-3 py-2"><?= (int)$r['month'] ?></td>
              <td class="px-3 py-2"><?= (int)$r['year'] ?></td>
              <td class="px-3 py-2"><?= number_format((float)$r['total_collection'], 2) ?></td>
              <td class="px-3 py-2"><?= number_format((float)$r['bank_deposit'], 2) ?></td>
              <td class="px-3 py-2 space-x-2">
                <a class="btn px-3 py-1.5 rounded-md text-xs bg-slate-900 text-white hover:bg-slate-800" href="account_view.php?id=<?= (int)$r['id'] ?>">View</a>
                <a class="btn secondary px-3 py-1.5 rounded-md text-xs border text-slate-700 hover:bg-slate-50" href="report_print.php?id=<?= (int)$r['id'] ?>" target="_blank">Print</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if (is_admin()): ?>
      <div class="footer-actions flex justify-end mt-4">
        <a class="btn px-4 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 text-sm" href="account_new.php">New Monthly Account</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
