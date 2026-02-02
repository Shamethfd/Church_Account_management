<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
require_once __DIR__ . '/config.php';

$tables_status = [];
$required_tables = ['users', 'monthly_accounts', 'weekly_collections'];

// Check database connection
$db_connected = false;
$db_error = null;
try {
    $pdo->query('SELECT 1');
    $db_connected = true;
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Check tables
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
                // Get column info
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

// Handle setup request
$setup_result = null;
if (isset($_POST['setup_db']) && $db_connected) {
    try {
        $sql = file_get_contents(__DIR__ . '/db.sql');
        $pdo->exec($sql);
        $setup_result = ['success' => true, 'message' => 'Database tables created successfully!'];
        
        // Refresh table status
        header('Location: db_check.php');
        exit;
    } catch (Exception $e) {
        $setup_result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Database Check - CCMC Accounts</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .status-ok { color: #28a745; font-weight: bold; }
    .status-error { color: #dc3545; font-weight: bold; }
    .db-info { margin: 20px 0; }
    .db-info table { width: 100%; border-collapse: collapse; }
    .db-info th, .db-info td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    .db-info th { background-color: #f8f9fa; }
    .column-list { font-size: 0.9em; color: #666; }
    .alert { padding: 15px; margin: 15px 0; border-radius: 4px; }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .btn:hover { background: #0056b3; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container">
    <div class="card">
      <h2>Database Status Check</h2>
      
      <?php if ($setup_result): ?>
        <div class="alert alert-<?= $setup_result['success'] ? 'success' : 'danger' ?>">
          <?= htmlspecialchars($setup_result['message']) ?>
        </div>
      <?php endif; ?>
      
      <div class="db-info">
        <h3>Connection Status</h3>
        <p>
          <?php if ($db_connected): ?>
            <span class="status-ok">✓ Connected to database</span>
          <?php else: ?>
            <span class="status-error">✗ Database connection failed</span><br>
            <small><?= htmlspecialchars($db_error) ?></small>
          <?php endif; ?>
        </p>
      </div>
      
      <?php if ($db_connected): ?>
        <div class="db-info">
          <h3>Tables Status</h3>
          <table>
            <thead>
              <tr>
                <th>Table Name</th>
                <th>Status</th>
                <th>Columns</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tables_status as $table => $status): ?>
                <tr>
                  <td><code><?= htmlspecialchars($table) ?></code></td>
                  <td>
                    <?php if ($status['exists']): ?>
                      <span class="status-ok">✓ Exists</span>
                    <?php else: ?>
                      <span class="status-error">✗ Missing</span>
                    <?php endif; ?>
                  </td>
                  <td class="column-list">
                    <?php if (!empty($status['columns'])): ?>
                      <?= implode(', ', array_map('htmlspecialchars', $status['columns'])) ?>
                    <?php elseif (isset($status['error'])): ?>
                      <span class="status-error"><?= htmlspecialchars($status['error']) ?></span>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <?php
        $all_tables_exist = true;
        foreach ($tables_status as $status) {
            if (!$status['exists']) {
                $all_tables_exist = false;
                break;
            }
        }
        ?>
        
        <?php if (!$all_tables_exist): ?>
          <div class="alert alert-danger">
            <strong>Warning:</strong> Some required tables are missing. Click the button below to create them.
          </div>
          <form method="post">
            <button type="submit" name="setup_db" class="btn" onclick="return confirm('This will create the missing database tables. Continue?')">
              Create Missing Tables
            </button>
          </form>
        <?php else: ?>
          <div class="alert alert-success">
            <strong>All required tables exist!</strong> Your database is properly configured.
          </div>
        <?php endif; ?>
      <?php endif; ?>
      
      <div style="margin-top: 30px;">
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
      </div>
    </div>
  </div>
</body>
</html>
