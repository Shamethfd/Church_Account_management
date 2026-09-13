<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
$createdUid = trim($_GET['uid'] ?? '');
$pageTitle = 'Add Family - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Congregation</p>
        <h1 class="page-title">Add family</h1>
        <p class="page-lede">Register a household before adding individual members.</p>
      </div>
    </div>

    <form class="panel" method="post" action="family_save.php">
      <?php if ($success === 1): ?>
      <div class="alert success">
        Family created successfully. Family ID: <strong><?= htmlspecialchars($createdUid) ?></strong>
      </div>
      <?php endif; ?>

      <fieldset class="fieldset">
        <legend>Household identity</legend>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Family name</label>
            <input type="text" name="family_name" required maxlength="150" placeholder="Perera Family">
          </div>
          <div class="field">
            <label>Head of family</label>
            <input type="text" name="head_name" required maxlength="120" placeholder="Name of family head">
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Contact &amp; address</legend>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Phone</label>
            <input type="text" name="phone" maxlength="30" placeholder="07xxxxxxxx">
          </div>
          <div class="field">
            <label>City</label>
            <input type="text" name="city" maxlength="100" placeholder="Colombo">
          </div>
          <div class="field span-all">
            <label>Address</label>
            <input type="text" name="address_line" maxlength="255" placeholder="No, street, area">
          </div>
          <div class="field span-all">
            <label>Notes</label>
            <textarea name="notes" rows="3" maxlength="1500" placeholder="Any important family details"></textarea>
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <a class="btn btn-secondary" href="member_management.php">Back</a>
        <button class="btn btn-primary" type="submit">Save family</button>
      </div>
    </form>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
