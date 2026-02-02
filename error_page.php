<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$error_message = $_SESSION['error_message'] ?? 'An error occurred.';
$error_details = $_SESSION['error_details'] ?? 'Please try again or contact support.';

// Clear the error messages from session after reading
unset($_SESSION['error_message']);
unset($_SESSION['error_details']);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Error - CCMC Accounts</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    .error-container {
      max-width: 600px;
      margin: 50px auto;
      padding: 30px;
      text-align: center;
    }
    .error-icon {
      font-size: 64px;
      color: #dc3545;
      margin-bottom: 20px;
    }
    .error-title {
      color: #dc3545;
      font-size: 24px;
      margin-bottom: 15px;
    }
    .error-message {
      color: #666;
      margin-bottom: 30px;
      line-height: 1.6;
    }
    .error-actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn {
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      display: inline-block;
    }
    .btn-primary {
      background-color: #007bff;
      color: white;
    }
    .btn-primary:hover {
      background-color: #0056b3;
    }
    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background-color: #545b62;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>
  <div class="container">
    <div class="card error-container">
      <div class="error-icon">⚠️</div>
      <h1 class="error-title"><?= htmlspecialchars($error_message) ?></h1>
      <p class="error-message"><?= htmlspecialchars($error_details) ?></p>
      <div class="error-actions">
        <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
        <a href="account_new.php" class="btn btn-primary">New Account</a>
        <a href="db_check.php" class="btn btn-secondary">Check Database</a>
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
      </div>
    </div>
  </div>
</body>
</html>
