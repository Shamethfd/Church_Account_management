<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
require_once __DIR__ . '/config.php';

$tables_status = [];
$required_tables = ['users', 'monthly_accounts', 'weekly_collections', 'app_users', 'families', 'family_members'];

$db_connected = false;
$db_error = null;
try {
    $pdo->query('SELECT 1');
    $db_connected = true;
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

if ($db_connected) {
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch() !== false;
            $tables_status[$table] = [
                'exists' => $exists,
                'columns' => []
            ];

            if ($exists) {
                $cols = $pdo->query("SHOW COLUMNS FROM $table");
                while ($col = $cols->fetch()) {
                    $tables_status[$table]['columns'][] = $col['Field'];
                }
            }
        } catch (Exception $e) {
            $tables_status[$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

$setup_result = null;
if (isset($_POST['setup_db']) && $db_connected) {
    try {
        $sql = file_get_contents(__DIR__ . '/db.sql');
        $pdo->exec($sql);
        $setup_result = ['success' => true, 'message' => 'Database tables created successfully!'];

        header('Location: db_check.php');
        exit;
    } catch (Exception $e) {
        $setup_result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

$all_tables_exist = true;
foreach ($tables_status as $status) {
    if (empty($status['exists'])) {
        $all_tables_exist = false;
        break;
    }
}

$pageTitle = 'Database Check - CCMC Accounts';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Administration</p>
        <h1 class="page-title">Database status</h1>
        <p class="page-lede">Confirm the connection and required tables before using member and account features.</p>
      </div>
      <div class="page-actions">
        <a href="dashboard.php" class="btn btn-secondary">Back to dashboard</a>
      </div>
    </div>

    <section class="panel">
      <?php if ($setup_result): ?>
        <div class="alert <?= $setup_result['success'] ? 'success' : 'error' ?>">
          <?= htmlspecialchars($setup_result['message']) ?>
        </div>
      <?php endif; ?>

      <div class="meta-grid" style="margin-bottom:18px">
        <div class="meta-item">
          <span>Connection</span>
          <strong class="<?= $db_connected ? 'status-ok' : 'status-error' ?>">
            <?= $db_connected ? 'Connected' : 'Failed' ?>
          </strong>
          <?php if (!$db_connected): ?>
            <p class="hint"><?= htmlspecialchars((string)$db_error) ?></p>
          <?php endif; ?>
        </div>
        <div class="meta-item">
          <span>Tables</span>
          <strong><?= $all_tables_exist ? 'Complete' : 'Action needed' ?></strong>
        </div>
      </div>

      <?php if ($db_connected): ?>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Table</th>
                <th>Status</th>
                <th>Columns</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tables_status as $table => $status): ?>
                <tr>
                  <td><code><?= htmlspecialchars($table) ?></code></td>
                  <td>
                    <?php if (!empty($status['exists'])): ?>
                      <span class="status-ok">Exists</span>
                    <?php else: ?>
                      <span class="status-error">Missing</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($status['columns'])): ?>
                      <?= implode(', ', array_map('htmlspecialchars', $status['columns'])) ?>
                    <?php elseif (isset($status['error'])): ?>
                      <span class="status-error"><?= htmlspecialchars($status['error']) ?></span>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if (!$all_tables_exist): ?>
          <div class="alert error" style="margin-top:16px">
            Some required tables are missing. Create them before continuing.
          </div>
          <form method="post">
            <div class="form-actions">
              <button type="submit" name="setup_db" class="btn btn-primary" onclick="return confirm('This will create the missing database tables. Continue?')">
                Create missing tables
              </button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert success" style="margin-top:16px">All required tables exist. The database is ready.</div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
