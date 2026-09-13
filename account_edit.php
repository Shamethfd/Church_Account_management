<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();

global $pdo;
$id = (int)($_GET['id'] ?? 0);
$stm = $pdo->prepare('SELECT * FROM monthly_accounts WHERE id = ?');
$stm->execute([$id]);
$acc = $stm->fetch();
if (!$acc) { http_response_code(404); echo 'Not found'; exit; }

$wstm = $pdo->prepare('SELECT * FROM weekly_collections WHERE account_id = ? ORDER BY week ASC');
$wstm->execute([$id]);
$weeks = [];
foreach ($wstm->fetchAll() as $w) { $weeks[(int)$w['week']] = $w; }
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
$pageTitle = 'Edit Monthly Account';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Accounts</p>
        <h1 class="page-title">Edit monthly account</h1>
        <p class="page-lede">Update collections and certification details for this saved period.</p>
      </div>
    </div>

    <form class="panel" method="post" action="account_update.php">
      <input type="hidden" name="id" value="<?php echo (int)$acc['id']; ?>">
      <fieldset class="fieldset">
        <legend>Account details</legend>
        <div class="form-grid cols-4">
          <div class="field">
            <label>Service</label>
            <select name="service" required>
              <option value="Sinhala" <?php if ((string)($acc['service'] ?? 'Sinhala')==='Sinhala') echo 'selected'; ?>>Sinhala Service</option>
              <option value="Tamil" <?php if ((string)($acc['service'] ?? '')==='Tamil') echo 'selected'; ?>>Tamil Service</option>
            </select>
          </div>
          <div class="field">
            <label>Month</label>
            <select name="month" required>
              <?php foreach ($months as $k=>$v): ?>
                <option value="<?= $k ?>" <?php if ((int)$acc['month']===$k) echo 'selected'; ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Year</label>
            <input type="number" name="year" min="2000" max="2100" value="<?php echo (int)$acc['year']; ?>" required>
          </div>
          <div class="field">
            <label>Day of service</label>
            <input type="text" name="service_day" value="<?php echo htmlspecialchars($acc['service_day']); ?>" required>
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Weekly collections</legend>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Week</th>
                <th>Date</th>
                <th class="num">Collection</th>
                <th class="num">Other offerings</th>
                <th class="num">Thanks offering</th>
                <th class="num">Christian service</th>
                <th class="num">Monthly offering</th>
                <th class="num">Week total</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($w=1;$w<=4;$w++): $row=$weeks[$w]??[]; ?>
              <tr>
                <td class="week-label"><?php echo $w; ?><?php if($w===1) echo 'st'; elseif($w===2) echo 'nd'; elseif($w===3) echo 'rd'; else echo 'th'; ?></td>
                <td><input type="date" name="weeks[<?= $w ?>][service_date]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['service_date'] ?? ''); ?>"></td>
                <td><input type="number" step="0.01" min="0" name="weeks[<?= $w ?>][collection_amount]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['collection_amount'] ?? ''); ?>"></td>
                <td><input type="number" step="0.01" min="0" name="weeks[<?= $w ?>][other_offerings]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['other_offerings'] ?? ''); ?>"></td>
                <td><input type="number" step="0.01" min="0" name="weeks[<?= $w ?>][thanks_offering]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['thanks_offering'] ?? ''); ?>"></td>
                <td><input type="number" step="0.01" min="0" name="weeks[<?= $w ?>][christian_service]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['christian_service'] ?? ''); ?>"></td>
                <td><input type="number" step="0.01" min="0" name="weeks[<?= $w ?>][monthly_offering]" data-week="<?= $w ?>" value="<?php echo htmlspecialchars($row['monthly_offering'] ?? ''); ?>"></td>
                <td class="num week-total" id="week_<?= $w ?>_sum">0.00</td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Banking summary</legend>
        <div class="form-grid cols-3">
          <div class="field">
            <label>Total collection (auto)</label>
            <input type="number" id="total_collection" name="total_collection" step="0.01" value="<?php echo htmlspecialchars($acc['total_collection']); ?>" readonly>
          </div>
          <div class="field">
            <label>Bank deposit</label>
            <input type="number" name="bank_deposit" step="0.01" min="0" value="<?php echo htmlspecialchars($acc['bank_deposit']); ?>">
          </div>
          <div class="field">
            <label>Bank slip reference</label>
            <input type="text" name="bank_slip_no" value="<?php echo htmlspecialchars($acc['bank_slip_no'] ?? ''); ?>">
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Certification &amp; signatures</legend>
        <div class="form-grid cols-3">
          <div class="field">
            <label>Prepared by (Treasurer)</label>
            <input type="text" name="prepared_by" value="<?php echo htmlspecialchars($acc['prepared_by'] ?? ''); ?>">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="prepared_date" value="<?php echo htmlspecialchars($acc['prepared_date'] ?? ''); ?>">
          </div>
          <div class="field">
            <label>Verified by (Circuit Steward)</label>
            <input type="text" name="verified_by" value="<?php echo htmlspecialchars($acc['verified_by'] ?? ''); ?>">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="verified_date" value="<?php echo htmlspecialchars($acc['verified_date'] ?? ''); ?>">
          </div>
          <div class="field">
            <label>Approved by</label>
            <input type="text" name="approved_name" value="<?php echo htmlspecialchars($acc['approved_name'] ?? ''); ?>">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="approved_date" value="<?php echo htmlspecialchars($acc['approved_date'] ?? ''); ?>">
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <a class="btn btn-secondary" href="account_view.php?id=<?= (int)$acc['id'] ?>">Cancel</a>
        <button class="btn btn-primary" type="submit">Save changes</button>
      </div>
    </form>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
