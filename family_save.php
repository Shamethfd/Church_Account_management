<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: family_new.php');
    exit;
}

$familyName = trim($_POST['family_name'] ?? '');
$headName = trim($_POST['head_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$addressLine = trim($_POST['address_line'] ?? '');
$city = trim($_POST['city'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($familyName === '' || $headName === '') {
    $_SESSION['error_message'] = 'Family name and head name are required.';
    $_SESSION['error_details'] = 'Please provide the required family details and try again.';
    header('Location: error_page.php');
    exit;
}

function generate_family_uid() {
    return 'FAM-' . strtoupper(bin2hex(random_bytes(5)));
}

$createdBy = is_admin() ? null : (int)(current_user()['id'] ?? 0);
if ($createdBy === 0) {
    $createdBy = null;
}

$pdo->beginTransaction();
try {
    $inserted = false;
    $familyId = 0;
    $familyUid = '';

    $stmt = $pdo->prepare('INSERT INTO families (family_uid, family_name, head_name, phone, address_line, city, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

    // Retry on rare UID collision; UID is immutable and never reassigned.
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $familyUid = generate_family_uid();
        try {
            $stmt->execute([$familyUid, $familyName, $headName, $phone ?: null, $addressLine ?: null, $city ?: null, $notes ?: null, $createdBy]);
            $familyId = (int)$pdo->lastInsertId();
            $inserted = true;
            break;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
        }
    }

    if (!$inserted || $familyId <= 0) {
        throw new RuntimeException('Unable to generate unique family ID.');
    }

    $pdo->commit();
    header('Location: member_new.php?family_id=' . $familyId . '&family_created=1&uid=' . urlencode($familyUid));
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('family_save failed: ' . $e->getMessage());

    $_SESSION['error_message'] = 'Failed to save family.';
    $_SESSION['error_details'] = 'Please verify the entered details and try again.';
    header('Location: error_page.php');
    exit;
}
