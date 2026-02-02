<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: account_new.php');
  exit;
}

$month = (int)($_POST['month'] ?? 0);
$year = (int)($_POST['year'] ?? 0);
$service_day = trim($_POST['service_day'] ?? 'Sunday');
$total_collection = (float)($_POST['total_collection'] ?? 0);
$bank_deposit = (float)($_POST['bank_deposit'] ?? 0);
$bank_slip_no = trim($_POST['bank_slip_no'] ?? '');
$prepared_by = trim($_POST['prepared_by'] ?? '');
$prepared_date = $_POST['prepared_date'] ?: null;
$verified_by = trim($_POST['verified_by'] ?? '');
$verified_date = $_POST['verified_date'] ?: null;
$approved_name = trim($_POST['approved_name'] ?? 'Rev. K. Sumithra N. Fernando');
$approved_date = $_POST['approved_date'] ?: null;
$weeks = $_POST['weeks'] ?? [];

$pdo->beginTransaction();
try {
  // Validate required fields
  if ($month < 1 || $month > 12) {
    throw new Exception('Invalid month value');
  }
  if ($year < 2000 || $year > 2100) {
    throw new Exception('Invalid year value');
  }
  
  // Detect if approved_date column exists for forward/backward compatibility
  $colCheck = $pdo->query("SHOW COLUMNS FROM monthly_accounts LIKE 'approved_date'");
  $hasApprovedDate = (bool)$colCheck->fetch();

  if ($hasApprovedDate) {
    $stm = $pdo->prepare('INSERT INTO `monthly_accounts` (`month`, `year`, `service_day`, `total_collection`, `bank_deposit`, `bank_slip_no`, `prepared_by`, `prepared_date`, `verified_by`, `verified_date`, `approved_name`, `approved_date`, `created_by`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stm->execute([$month,$year,$service_day,$total_collection,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,$approved_date,current_user()['id']]);
  } else {
    $stm = $pdo->prepare('INSERT INTO `monthly_accounts` (`month`, `year`, `service_day`, `total_collection`, `bank_deposit`, `bank_slip_no`, `prepared_by`, `prepared_date`, `verified_by`, `verified_date`, `approved_name`, `created_by`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stm->execute([$month,$year,$service_day,$total_collection,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,current_user()['id']]);
  }
  $account_id = (int)$pdo->lastInsertId();

  $wstm = $pdo->prepare('INSERT INTO `weekly_collections` (`account_id`, `week`, `service_date`, `collection_amount`, `other_offerings`, `thanks_offering`, `christian_service`, `monthly_offering`) VALUES (?,?,?,?,?,?,?,?)');
  for ($w=1;$w<=4;$w++) {
    $row = $weeks[$w] ?? [];
    $service_date = $row['service_date'] ?? null;
    $collection_amount = isset($row['collection_amount']) ? (float)$row['collection_amount'] : 0;
    $other_offerings = isset($row['other_offerings']) ? (float)$row['other_offerings'] : 0;
    $thanks_offering = isset($row['thanks_offering']) ? (float)$row['thanks_offering'] : 0;
    $christian_service = isset($row['christian_service']) ? (float)$row['christian_service'] : 0;
    $monthly_offering = isset($row['monthly_offering']) ? (float)$row['monthly_offering'] : 0;
    $wstm->execute([$account_id,$w,$service_date,$collection_amount,$other_offerings,$thanks_offering,$christian_service,$monthly_offering]);
  }

  $pdo->commit();
  header('Location: account_view.php?id=' . $account_id);
  exit;
} catch (Throwable $e) {
  $pdo->rollBack();
  // Log detailed error for troubleshooting without exposing details to users
  $logMsg = '[' . date('c') . "] account_save failed: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
  error_log($logMsg);
  
  // Determine specific error message
  $errorMsg = 'Failed to save. Please try again.';
  $errorDetails = 'There was an error saving the monthly account. Please check your input and try again.';
  
  if (strpos($e->getMessage(), 'users') !== false && strpos($e->getMessage(), 'foreign key') !== false) {
    $errorDetails = 'Invalid user reference. Please make sure you are logged in properly.';
  } elseif (strpos($e->getMessage(), 'month') !== false || strpos($e->getMessage(), 'year') !== false) {
    $errorDetails = 'Invalid month or year value. Please check your input.';
  } elseif (strpos($e->getMessage(), 'Duplicate') !== false) {
    $errorDetails = 'This monthly account may already exist. Please check existing records.';
  } elseif (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
    $errorDetails = 'Database tables are missing. Please run the database setup script (db.sql).';
  }
  
  // Display error page
  $_SESSION['error_message'] = $errorMsg;
  $_SESSION['error_details'] = $errorDetails;
  header('Location: error_page.php');
  exit;
}
