<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();

global $pdo;

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$errors = [];
$success = '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_users (\n  id INT AUTO_INCREMENT PRIMARY KEY,\n  number VARCHAR(30) NOT NULL UNIQUE,\n  name VARCHAR(100) NOT NULL,\n  password VARCHAR(255) NOT NULL,\n  service ENUM('Sinhala','Tamil') NOT NULL,\n  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    $errors[] = 'Initialization error.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
    if (($_POST['action'] ?? '') === 'delete_user') {
      $userId = (int)($_POST['user_id'] ?? 0);
      if ($userId <= 0) {
        $errors[] = 'Invalid user.';
      } else {
        try {
          $deleteStmt = $pdo->prepare('DELETE FROM app_users WHERE id = ?');
          $deleteStmt->execute([$userId]);
          $success = $deleteStmt->rowCount() ? 'User deleted successfully.' : 'User was not found.';
        } catch (Throwable $e) {
          $errors[] = 'Failed to delete user.';
        }
      }
    } else {
        $number = trim($_POST['number'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $service = $_POST['service'] ?? '';
        if ($number === '' || $name === '' || $password === '' || ($service !== 'Sinhala' && $service !== 'Tamil')) {
            $errors[] = 'All fields are required and service must be Sinhala or Tamil.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare('INSERT INTO app_users (number, name, password, service) VALUES (?,?,?,?)');
                $stmt->execute([$number, $name, $hash, $service]);
                $success = 'User added successfully.';
            } catch (Throwable $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $errors[] = 'Number already exists.';
                } else {
                    $errors[] = 'Failed to add user.';
                }
            }
        }
            }
    }
}

try {
    $listStmt = $pdo->query('SELECT id, number, name, service, created_at FROM app_users ORDER BY created_at DESC');
    $users = $listStmt->fetchAll();
} catch (Throwable $e) {
    $users = [];
}
$pageTitle = 'Admin - Manage Users';
require __DIR__ . '/includes/head.php';
?>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <main class="page">
    <div class="page-head">
      <div>
        <p class="page-kicker">Administration</p>
        <h1 class="page-title">User access</h1>
        <p class="page-lede">Create member logins and assign Sinhala or Tamil service access.</p>
      </div>
      <div class="page-actions">
        <a class="btn btn-secondary" href="db_check.php">Database check</a>
      </div>
    </div>

    <div class="form-grid cols-2">
      <section class="panel">
        <h2>Add user</h2>
        <?php foreach ($errors as $e): ?><div class="alert error"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
        <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
          <fieldset class="fieldset">
            <legend>Login details</legend>
            <div class="form-grid">
              <div class="field">
                <label>Number</label>
                <input type="text" name="number" required>
              </div>
              <div class="field">
                <label>Name</label>
                <input type="text" name="name" required>
              </div>
              <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
              </div>
              <div class="field">
                <label>Service access</label>
                <select name="service" required>
                  <option value="Sinhala">Sinhala Service</option>
                  <option value="Tamil">Tamil Service</option>
                </select>
              </div>
            </div>
          </fieldset>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Add user</button>
          </div>
        </form>
      </section>

      <section class="panel">
        <h2>Existing users</h2>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Number</th>
                <th>Name</th>
                <th>Service</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td><?php echo htmlspecialchars($u['number']); ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td><span class="badge"><?php echo htmlspecialchars($u['service']); ?></span></td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                <td>
                  <form method="post" onsubmit="return confirm('Delete this user?');">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (!$users): ?>
              <tr><td colspan="5">No users yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
