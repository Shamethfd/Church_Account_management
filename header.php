<?php require_once __DIR__ . '/includes/auth.php'; $user = current_user(); ?>
<header class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b">
  <div class="container max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <div>
      <div class="title text-base md:text-lg font-semibold text-slate-900">COLOMBO CITY MISSION CIRCUIT</div>
      <div class="subtitle text-slate-600">Monthly Account Management</div>
    </div>
    <nav class="flex items-center gap-2">
      <?php if ($user): ?>
        <a class="btn px-3 py-2 rounded-md text-sm bg-slate-900 text-white hover:bg-slate-800" href="dashboard.php">Dashboard</a>
        <?php if (is_admin()): ?><a class="btn px-3 py-2 rounded-md text-sm border hover:bg-slate-50" href="account_new.php">New Monthly Account</a><?php endif; ?>
        <a class="btn secondary px-3 py-2 rounded-md text-sm border text-slate-700 hover:bg-slate-50" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn px-3 py-2 rounded-md text-sm bg-slate-900 text-white hover:bg-slate-800" href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
