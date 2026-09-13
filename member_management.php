<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/config.php';

global $pdo;

$memberName = trim($_GET['member_name'] ?? '');
$jobFilter = trim($_GET['job'] ?? '');
$familyUidFilter = trim($_GET['family_uid'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$familiesPerPage = 10;

$familyCount = 0;
$memberCount = 0;
$families = [];
$membersByFamily = [];
$totalFamilies = 0;
$totalPages = 1;

try {
    $familyCount = (int)$pdo->query('SELECT COUNT(*) FROM families')->fetchColumn();
    $memberCount = (int)$pdo->query('SELECT COUNT(*) FROM family_members')->fetchColumn();

    $where = [];
    $params = [];

    if ($memberName !== '') {
        $where[] = 'fm.member_name LIKE ?';
        $params[] = '%' . $memberName . '%';
    }
    if ($jobFilter !== '') {
        $where[] = 'fm.job_title LIKE ?';
        $params[] = '%' . $jobFilter . '%';
    }
    if ($familyUidFilter !== '') {
        $where[] = 'f.family_uid = ?';
        $params[] = $familyUidFilter;
    }

    $countSql = 'SELECT COUNT(DISTINCT f.id) FROM families f';
    if (!empty($where)) {
      $countSql .= ' INNER JOIN family_members fm ON fm.family_id = f.id WHERE ' . implode(' AND ', $where);
    }
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalFamilies = (int)$countStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalFamilies / $familiesPerPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $familiesPerPage;

    if (!empty($where)) {
        $sql = 'SELECT DISTINCT f.id, f.family_uid, f.family_name, f.head_name, f.phone, f.address_line, f.city, f.notes, f.created_at
                FROM families f
                INNER JOIN family_members fm ON fm.family_id = f.id
                WHERE ' . implode(' AND ', $where) . '
          ORDER BY f.created_at DESC, f.id DESC
          LIMIT ' . $familiesPerPage . ' OFFSET ' . $offset;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $families = $stmt->fetchAll();
    } else {
      $stmt = $pdo->query('SELECT id, family_uid, family_name, head_name, phone, address_line, city, notes, created_at FROM families ORDER BY created_at DESC, id DESC LIMIT ' . $familiesPerPage . ' OFFSET ' . $offset);
        $families = $stmt->fetchAll();
    }

    if (!empty($families)) {
        $ids = array_map(static function ($row) {
            return (int)$row['id'];
        }, $families);

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $mStmt = $pdo->prepare('SELECT id, family_id, member_name, age, date_of_birth, job_title, relationship_to_head, phone, email, remarks FROM family_members WHERE family_id IN (' . $placeholders . ') ORDER BY family_id ASC, member_name ASC');
        $mStmt->execute($ids);
        $allMembers = $mStmt->fetchAll();

        foreach ($allMembers as $member) {
            $fid = (int)$member['family_id'];
            if (!isset($membersByFamily[$fid])) {
                $membersByFamily[$fid] = [];
            }
            $membersByFamily[$fid][] = $member;
        }
    }
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Member management tables are not ready.';
  $_SESSION['error_details'] = 'Open db_check.php as admin and click Create Missing Tables, or run db.sql for fresh setup / alter.sql for existing setup.';
    header('Location: error_page.php');
    exit;
}
$pageTitle = 'Member Management - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Congregation</p>
        <h1 class="page-title">Member management</h1>
        <p class="page-lede">Create families first, then add members under each household.</p>
      </div>
      <div class="page-actions">
        <a class="btn btn-gold" href="family_new.php">Add family</a>
        <a class="btn btn-primary" href="member_new.php">Add member</a>
      </div>
    </div>

    <section class="stats">
      <div class="stat"><span>Families</span><strong><?= $familyCount ?></strong></div>
      <div class="stat"><span>Members</span><strong><?= $memberCount ?></strong></div>
      <div class="stat"><span>Showing</span><strong><?= count($families) ?></strong></div>
      <div class="stat"><span>Directory</span><strong>Active</strong></div>
    </section>

    <section class="panel">
      <h2>Search families</h2>
      <form method="get">
        <fieldset class="fieldset">
          <legend>Filters</legend>
          <div class="form-grid cols-3">
            <div class="field">
              <label>Member name</label>
              <input type="text" name="member_name" value="<?= htmlspecialchars($memberName) ?>" placeholder="Type member name">
            </div>
            <div class="field">
              <label>Job</label>
              <input type="text" name="job" value="<?= htmlspecialchars($jobFilter) ?>" placeholder="Teacher / Engineer">
            </div>
            <div class="field">
              <label>Family ID</label>
              <input type="text" name="family_uid" value="<?= htmlspecialchars($familyUidFilter) ?>" placeholder="FAM-XXXXXXXXXX">
            </div>
          </div>
        </fieldset>
        <div class="form-actions">
          <a class="btn btn-secondary" href="member_management.php">Reset</a>
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
      </form>
    </section>

    <?php if (empty($families)): ?>
    <section class="panel">
      <div class="empty-state">No results found for the current search.</div>
    </section>
    <?php else: ?>
      <?php foreach ($families as $family): ?>
      <section class="panel family-card">
        <div class="panel-head">
          <div>
            <h2><?= htmlspecialchars($family['family_name']) ?></h2>
            <p class="page-lede">Family ID <?= htmlspecialchars($family['family_uid']) ?></p>
          </div>
          <a class="btn btn-secondary btn-sm" href="member_new.php?family_id=<?= (int)$family['id'] ?>">Add member</a>
        </div>
        <div class="meta-grid">
          <div class="meta-item"><span>Head of family</span><strong><?= htmlspecialchars($family['head_name']) ?></strong></div>
          <div class="meta-item"><span>Phone</span><strong><?= htmlspecialchars((string)$family['phone'] ?: '—') ?></strong></div>
          <div class="meta-item"><span>Address</span><strong><?= htmlspecialchars(trim(((string)$family['address_line']) . ' ' . ((string)$family['city'])) ?: '—') ?></strong></div>
        </div>
        <?php if (!empty($family['notes'])): ?>
          <p class="page-lede"><?= nl2br(htmlspecialchars((string)$family['notes'])) ?></p>
        <?php endif; ?>

        <h3>Family members</h3>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Relationship</th>
                <th>Age</th>
                <th>Date of birth</th>
                <th>Job</th>
                <th>Phone</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <?php $fMembers = $membersByFamily[(int)$family['id']] ?? []; ?>
              <?php if (empty($fMembers)): ?>
              <tr>
                <td colspan="7">No members added yet.</td>
              </tr>
              <?php else: ?>
                <?php foreach ($fMembers as $m): ?>
                <tr>
                  <td><?= htmlspecialchars($m['member_name']) ?></td>
                  <td><?= htmlspecialchars((string)$m['relationship_to_head']) ?></td>
                  <td><?= $m['age'] === null ? '' : (int)$m['age'] ?></td>
                  <td><?= htmlspecialchars((string)$m['date_of_birth']) ?></td>
                  <td><?= htmlspecialchars((string)$m['job_title']) ?></td>
                  <td><?= htmlspecialchars((string)$m['phone']) ?></td>
                  <td><?= htmlspecialchars((string)$m['email']) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Family pages">
      <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
        <?php $pageQuery = http_build_query(array_filter([
            'member_name' => $memberName,
            'job' => $jobFilter,
            'family_uid' => $familyUidFilter,
            'page' => $pageNumber,
        ], static function ($value) { return $value !== ''; })); ?>
        <a class="pagination-link<?= $pageNumber === $page ? ' is-active' : '' ?>" href="member_management.php?<?= htmlspecialchars($pageQuery) ?>"><?= $pageNumber ?></a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
