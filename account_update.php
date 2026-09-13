<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_admin();
require_once __DIR__ . '/config.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: dashboard.php');
  exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo 'Invalid request'; exit; }

$month = (int)($_POST['month'] ?? 0);
$year = (int)($_POST['year'] ?? 0);
$service_day = trim($_POST['service_day'] ?? 'Sunday');
$service = trim($_POST['service'] ?? 'Sinhala');
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
  if ($month < 1 || $month > 12) throw new Exception('Invalid month');
  if ($year < 2000 || $year > 2100) throw new Exception('Invalid year');

  // Detect columns
  $svcCheck = $pdo->query("SHOW COLUMNS FROM monthly_accounts LIKE 'service'");
  $hasService = (bool)$svcCheck->fetch();
  $apCheck = $pdo->query("SHOW COLUMNS FROM monthly_accounts LIKE 'approved_date'");
  $hasApprovedDate = (bool)$apCheck->fetch();

  // Update monthly_accounts header fields first (total will be recomputed later)
  if ($hasService && $hasApprovedDate) {
    $ustm = $pdo->prepare('UPDATE monthly_accounts SET month=?, year=?, service_day=?, service=?, bank_deposit=?, bank_slip_no=?, prepared_by=?, prepared_date=?, verified_by=?, verified_date=?, approved_name=?, approved_date=? WHERE id=?');
    $ustm->execute([$month,$year,$service_day,$service,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,$approved_date,$id]);
  } elseif ($hasService && !$hasApprovedDate) {
    $ustm = $pdo->prepare('UPDATE monthly_accounts SET month=?, year=?, service_day=?, service=?, bank_deposit=?, bank_slip_no=?, prepared_by=?, prepared_date=?, verified_by=?, verified_date=?, approved_name=? WHERE id=?');
    $ustm->execute([$month,$year,$service_day,$service,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,$id]);
  } elseif (!$hasService && $hasApprovedDate) {
    $ustm = $pdo->prepare('UPDATE monthly_accounts SET month=?, year=?, service_day=?, bank_deposit=?, bank_slip_no=?, prepared_by=?, prepared_date=?, verified_by=?, verified_date=?, approved_name=?, approved_date=? WHERE id=?');
    $ustm->execute([$month,$year,$service_day,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,$approved_date,$id]);
  } else {
    $ustm = $pdo->prepare('UPDATE monthly_accounts SET month=?, year=?, service_day=?, bank_deposit=?, bank_slip_no=?, prepared_by=?, prepared_date=?, verified_by=?, verified_date=?, approved_name=? WHERE id=?');
    $ustm->execute([$month,$year,$service_day,$bank_deposit,$bank_slip_no,$prepared_by,$prepared_date,$verified_by,$verified_date,$approved_name,$id]);
  }

  // Upsert weekly rows only for weeks with data, delete otherwise
  $selWeek = $pdo->prepare('SELECT id FROM weekly_collections WHERE account_id=? AND week=?');
  $insWeek = $pdo->prepare('INSERT INTO weekly_collections (account_id, week, service_date, collection_amount, other_offerings, thanks_offering, christian_service, monthly_offering) VALUES (?,?,?,?,?,?,?,?)');
  $updWeek = $pdo->prepare('UPDATE weekly_collections SET service_date=?, collection_amount=?, other_offerings=?, thanks_offering=?, christian_service=?, monthly_offering=? WHERE account_id=? AND week=?');
  $delWeek = $pdo->prepare('DELETE FROM weekly_collections WHERE account_id=? AND week=?');

  for ($w=1;$w<=4;$w++) {
    $row = $weeks[$w] ?? [];
    $service_date = $row['service_date'] ?? null;
    $collection_amount = isset($row['collection_amount']) && $row['collection_amount'] !== '' ? (float)$row['collection_amount'] : 0;
    $other_offerings = isset($row['other_offerings']) && $row['other_offerings'] !== '' ? (float)$row['other_offerings'] : 0;
    $thanks_offering = isset($row['thanks_offering']) && $row['thanks_offering'] !== '' ? (float)$row['thanks_offering'] : 0;
    $christian_service = isset($row['christian_service']) && $row['christian_service'] !== '' ? (float)$row['christian_service'] : 0;
    $monthly_offering = isset($row['monthly_offering']) && $row['monthly_offering'] !== '' ? (float)$row['monthly_offering'] : 0;

    $row_sum = $collection_amount + $other_offerings + $thanks_offering + $christian_service + $monthly_offering;
    $hasAny = ($service_date && $service_date !== '') || $row_sum > 0;

    if ($hasAny) {
      $selWeek->execute([$id,$w]);
      $exists = (bool)$selWeek->fetchColumn();
      if ($exists) {
        $updWeek->execute([$service_date,$collection_amount,$other_offerings,$thanks_offering,$christian_service,$monthly_offering,$id,$w]);
      } else {
        $insWeek->execute([$id,$w,$service_date,$collection_amount,$other_offerings,$thanks_offering,$christian_service,$monthly_offering]);
      }
    } else {
      // No data provided for this week: delete if exists
      $delWeek->execute([$id,$w]);
    }
  }

  // Recompute total from stored weekly rows
  $sumStmt = $pdo->prepare('SELECT COALESCE(SUM(collection_amount + other_offerings + thanks_offering + christian_service + monthly_offering),0) AS total FROM weekly_collections WHERE account_id=?');
  $sumStmt->execute([$id]);
  $total = (float)$sumStmt->fetchColumn();

  $updTotal = $pdo->prepare('UPDATE monthly_accounts SET total_collection=? WHERE id=?');
  $updTotal->execute([$total,$id]);

  $pdo->commit();
  header('Location: account_view.php?id=' . $id);
  exit;
} catch (Throwable $e) {
  $pdo->rollBack();
  error_log('account_update failed: '.$e->getMessage());
  $_SESSION['error_message'] = 'Failed to update.';
  $_SESSION['error_details'] = 'There was an error saving the monthly account updates. Please try again.';
  header('Location: error_page.php');
  exit;
}
