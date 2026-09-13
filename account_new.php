<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
$pageTitle = 'New Monthly Account - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Accounts</p>
        <h1 class="page-title">New monthly account</h1>
        <p class="page-lede">Enter weekly collections, bank details, and certification in clearly grouped sections.</p>
      </div>
    </div>

    <form class="panel" method="post" action="account_save.php">
      <fieldset class="fieldset">
        <legend>Account details</legend>
        <div class="form-grid cols-4">
          <div class="field">
            <label>Church name</label>
            <input type="text" value="COLOMBO CITY MISSION CIRCUIT" readonly>
          </div>
          <div class="field">
            <label for="service">Service</label>
            <select id="service" name="service" required>
              <option value="Sinhala">Sinhala Service</option>
              <option value="Tamil">Tamil Service</option>
            </select>
          </div>
          <div class="field">
            <label for="month">Month</label>
            <select id="month" name="month" required>
              <?php foreach ($months as $k=>$v): ?>
                <option value="<?= $k ?>"><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="year">Year</label>
            <input id="year" type="number" name="year" min="2000" max="2100" value="<?= date('Y') ?>" required>
          </div>
          <div class="field">
            <label for="service_day">Day of service</label>
            <input id="service_day" type="text" name="service_day" value="Sunday" required>
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
              <?php for ($w=1;$w<=4;$w++): ?>
              <tr>
                <td class="week-label"><?= $w ?><?php if($w===1) echo 'st'; elseif($w===2) echo 'nd'; elseif($w===3) echo 'rd'; else echo 'th'; ?></td>
                <td><input type="date" name="weeks[<?= $w ?>][service_date]" data-week="<?= $w ?>"></td>
                <td><input type="number" name="weeks[<?= $w ?>][collection_amount]" step="0.01" min="0" data-week="<?= $w ?>"></td>
                <td><input type="number" name="weeks[<?= $w ?>][other_offerings]" step="0.01" min="0" data-week="<?= $w ?>"></td>
                <td><input type="number" name="weeks[<?= $w ?>][thanks_offering]" step="0.01" min="0" data-week="<?= $w ?>"></td>
                <td><input type="number" name="weeks[<?= $w ?>][christian_service]" step="0.01" min="0" data-week="<?= $w ?>"></td>
                <td><input type="number" name="weeks[<?= $w ?>][monthly_offering]" step="0.01" min="0" data-week="<?= $w ?>"></td>
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
            <label for="total_collection">Total collection (auto)</label>
            <input id="total_collection" type="number" name="total_collection" step="0.01" readonly>
          </div>
          <div class="field">
            <label for="bank_deposit">Bank deposit</label>
            <input id="bank_deposit" type="number" name="bank_deposit" step="0.01" min="0" required>
          </div>
          <div class="field">
            <label for="bank_slip_no">Bank slip reference</label>
            <input id="bank_slip_no" type="text" name="bank_slip_no">
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Certification &amp; signatures</legend>
        <div class="form-grid cols-3">
          <div class="field">
            <label>Prepared by (Treasurer)</label>
            <input type="text" name="prepared_by">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="prepared_date">
          </div>
          <div class="field">
            <label>Verified by (Circuit Steward)</label>
            <input type="text" name="verified_by">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="verified_date">
          </div>
          <div class="field">
            <label>Approved by</label>
            <input type="text" name="approved_name" value="Rev. K. Sumithra N. Fernando">
            <label style="margin-top:12px">Date</label>
            <input type="date" name="approved_date" value="">
          </div>
        </div>
        <div class="field" style="margin-top:16px">
          <label>Church seal</label>
          <div class="seal-box">Official seal</div>
        </div>
      </fieldset>

      <div class="form-actions">
        <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        <button class="btn btn-primary" type="submit">Save monthly account</button>
      </div>
    </form>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
