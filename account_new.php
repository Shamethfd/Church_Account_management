<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New Monthly Account - CCMC Accounts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/styles.css">
  <script defer src="assets/js/app.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container max-w-6xl mx-auto px-4">
    <form class="card rounded-xl border bg-white p-6 shadow-sm" method="post" action="account_save.php">
      <h2 class="text-xl font-semibold mb-4">Monthly Account Entry</h2>
      <div class="flex gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Church Name</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" value="COLOMBO CITY MISSION CIRCUIT" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Month</label>
          <select class="mt-1 w-full rounded-lg border-slate-300" name="month" required>
            <?php foreach ($months as $k=>$v): ?>
              <option value="<?= $k ?>"><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Year</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="number" name="year" min="2000" max="2100" value="<?= date('Y') ?>" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Day of Service</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" name="service_day" value="Sunday" required>
        </div>
      </div>

      <h3 class="mt-6 font-semibold">Weekly Collections</h3>
      <table class="table w-full text-sm">
        <thead>
          <tr class="bg-slate-100 text-slate-700">
            <th class="px-2 py-1 text-left">Week</th>
            <th class="px-2 py-1 text-left">Date</th>
            <th class="px-2 py-1 text-left">Collection Amount (Rs.)</th>
            <th class="px-2 py-1 text-left">Other Offerings</th>
            <th class="px-2 py-1 text-left">Thanks Offering</th>
            <th class="px-2 py-1 text-left">Christian Service</th>
            <th class="px-2 py-1 text-left">Monthly Offering</th>
            <th class="px-2 py-1 text-left">Week Total</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($w=1;$w<=4;$w++): ?>
          <tr class="border-b last:border-0">
            <td class="px-2 py-1"><?= $w ?><?php if($w===1) echo 'st'; elseif($w===2) echo 'nd'; elseif($w===3) echo 'rd'; else echo 'th'; ?></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="date" name="weeks[<?= $w ?>][service_date]" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="number" name="weeks[<?= $w ?>][collection_amount]" step="0.01" min="0" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="number" name="weeks[<?= $w ?>][other_offerings]" step="0.01" min="0" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="number" name="weeks[<?= $w ?>][thanks_offering]" step="0.01" min="0" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="number" name="weeks[<?= $w ?>][christian_service]" step="0.01" min="0" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><input class="w-full rounded-lg border-slate-300" type="number" name="weeks[<?= $w ?>][monthly_offering]" step="0.01" min="0" data-week="<?= $w ?>"></td>
            <td class="px-2 py-1"><strong id="week_<?= $w ?>_sum">0.00</strong></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>

      <div class="flex gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Total Collection (auto)</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="number" id="total_collection" name="total_collection" step="0.01" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Bank Deposit</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="number" name="bank_deposit" step="0.01" min="0" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Bank Slip Reference Number</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" name="bank_slip_no">
        </div>
      </div>

      <h3 class="mt-6 font-semibold">Certification & Signatures</h3>
      <div class="flex gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Prepared by (Treasurer)</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" name="prepared_by">
          <label class="block text-sm font-medium text-slate-700 mt-3">Date</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="date" name="prepared_date">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Verified by (Circuit Steward)</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" name="verified_by">
          <label class="block text-sm font-medium text-slate-700 mt-3">Date</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="date" name="verified_date">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Approved by (Name)</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="text" name="approved_name" value="Rev. K. Sumithra N. Fernando">
          <label class="block text-sm font-medium text-slate-700 mt-3">Date</label>
          <input class="mt-1 w-full rounded-lg border-slate-300" type="date" name="approved_date" value="">
        </div>
      </div>

      <div style="margin-top:12px;">
        <label class="block text-sm font-medium text-slate-700">Church Seal</label>
        <div class="seal-box mt-1"></div>
      </div>

      <div class="footer-actions flex justify-end gap-2 mt-4">
        <a class="btn secondary px-4 py-2 rounded-md border text-slate-700 hover:bg-slate-50" href="dashboard.php">Cancel</a>
        <button class="px-4 py-2 rounded-md bg-slate-900 text-white hover:bg-slate-800 text-sm" type="submit">Save Monthly Account</button>
      </div>
    </form>
  </div>
</body>
</html>
