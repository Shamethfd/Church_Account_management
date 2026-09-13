<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
  return;
}
$appLayout = true;
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
if (!function_exists('nav_active')) {
  function nav_active($pages, $currentPage) {
    $pages = (array)$pages;
    return in_array($currentPage, $pages, true) ? ' is-active' : '';
  }
}
?>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="dashboard.php">
      <span class="brand-mark">CC</span>
      <span>
        <strong>CCMC Accounts</strong>
        <small>Colombo City Mission</small>
      </span>
    </a>

    <nav class="sidebar-nav">
      <p class="nav-label">Main</p>
      <a class="nav-link<?= nav_active('dashboard.php', $currentPage) ?>" href="dashboard.php">Dashboard</a>

      <p class="nav-label">Congregation</p>
      <a class="nav-link<?= nav_active('member_management.php', $currentPage) ?>" href="member_management.php">Families &amp; members</a>
      <a class="nav-link<?= nav_active('family_new.php', $currentPage) ?>" href="family_new.php">Add family</a>
      <a class="nav-link<?= nav_active('member_new.php', $currentPage) ?>" href="member_new.php">Add member</a>

      <?php if (is_admin()): ?>
      <p class="nav-label">Accounts</p>
      <a class="nav-link<?= nav_active(['account_new.php','account_edit.php','account_view.php'], $currentPage) ?>" href="account_new.php">New monthly account</a>

      <p class="nav-label">System</p>
      <a class="nav-link<?= nav_active('admin.php', $currentPage) ?>" href="admin.php">User access</a>
      <a class="nav-link<?= nav_active('db_check.php', $currentPage) ?>" href="db_check.php">Database check</a>
      <?php endif; ?>
    </nav>

    <div class="sidebar-user">
      <div>
        <strong><?= htmlspecialchars($user['name'] ?? 'User') ?></strong>
        <small><?= is_admin() ? 'Administrator' : htmlspecialchars($user['service'] ?? 'Member') ?></small>
      </div>
      <a class="nav-link logout" href="logout.php">Sign out</a>
    </div>
  </aside>

  <div class="workspace">
    <header class="topbar">
      <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu">Menu</button>
      <div class="topbar-title">Monthly Account Management</div>
      <a class="btn btn-secondary btn-sm" href="logout.php">Sign out</a>
    </header>
