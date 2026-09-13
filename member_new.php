<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/config.php';

global $pdo;

$familyId = (int)($_GET['family_id'] ?? 0);
$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
$familyCreated = isset($_GET['family_created']) ? (int)$_GET['family_created'] : 0;
$uid = trim($_GET['uid'] ?? '');

$famStmt = $pdo->query('SELECT id, family_uid, family_name FROM families ORDER BY created_at DESC, id DESC');
$families = $famStmt->fetchAll();

$selectedFamily = null;
if ($familyId > 0) {
    $one = $pdo->prepare('SELECT id, family_uid, family_name FROM families WHERE id = ?');
    $one->execute([$familyId]);
    $selectedFamily = $one->fetch();
}
$pageTitle = 'Add Member - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Congregation</p>
        <h1 class="page-title">Add family member</h1>
        <p class="page-lede">Attach a person to an existing family record.</p>
      </div>
    </div>

    <form class="panel" method="post" action="member_save.php">
      <?php if ($familyCreated === 1 && $uid !== ''): ?>
      <div class="alert success">
        Family added successfully. Family ID: <strong><?= htmlspecialchars($uid) ?></strong>. Add members below.
      </div>
      <?php endif; ?>

      <?php if ($success === 1): ?>
      <div class="alert success">Member saved successfully.</div>
      <?php endif; ?>

      <?php if (empty($families)): ?>
      <div class="alert error">No families found. Please create a family first.</div>
      <div class="form-actions">
        <a class="btn btn-primary" href="family_new.php">Add family</a>
      </div>
      <?php else: ?>
      <fieldset class="fieldset">
        <legend>Household</legend>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Family</label>
            <select name="family_id" required>
              <option value="">Select family</option>
              <?php foreach ($families as $family): ?>
                <option value="<?= (int)$family['id'] ?>" <?= ((int)$family['id'] === $familyId) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($family['family_uid'] . ' - ' . $family['family_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Relationship to head</label>
            <input type="text" name="relationship_to_head" maxlength="80" placeholder="Father / Mother / Son / Daughter">
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Personal details</legend>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Name</label>
            <input type="text" name="member_name" required maxlength="150" placeholder="Member full name">
          </div>
          <div class="field">
            <label>Age</label>
            <input type="number" name="age" min="0" max="120" placeholder="Age">
          </div>
          <div class="field">
            <label>Date of birth</label>
            <input type="date" name="date_of_birth">
          </div>
          <div class="field">
            <label>Job</label>
            <input type="text" name="job_title" maxlength="150" placeholder="Teacher / Engineer / etc.">
          </div>
        </div>
      </fieldset>

      <fieldset class="fieldset">
        <legend>Contact</legend>
        <div class="form-grid cols-2">
          <div class="field">
            <label>Phone</label>
            <input type="text" name="phone" maxlength="30" placeholder="07xxxxxxxx">
          </div>
          <div class="field">
            <label>Email</label>
            <input type="email" name="email" maxlength="150" placeholder="example@email.com">
          </div>
          <div class="field span-all">
            <label>Remarks</label>
            <textarea name="remarks" rows="3" maxlength="1500" placeholder="Optional notes"></textarea>
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <a class="btn btn-secondary" href="member_management.php">Back</a>
        <button class="btn btn-primary" type="submit">Save member</button>
      </div>
      <?php endif; ?>
    </form>

    <?php if ($selectedFamily): ?>
    <section class="panel">
      <h2>Selected family</h2>
      <div class="meta-grid">
        <div class="meta-item"><span>ID</span><strong><?= htmlspecialchars($selectedFamily['family_uid']) ?></strong></div>
        <div class="meta-item"><span>Name</span><strong><?= htmlspecialchars($selectedFamily['family_name']) ?></strong></div>
      </div>
    </section>
    <?php endif; ?>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
