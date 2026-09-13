<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: member_new.php');
    exit;
}

$familyId = (int)($_POST['family_id'] ?? 0);
$memberName = trim($_POST['member_name'] ?? '');
$ageInput = trim((string)($_POST['age'] ?? ''));
$age = $ageInput === '' ? null : (int)$ageInput;
$dateOfBirth = trim($_POST['date_of_birth'] ?? '');
$jobTitle = trim($_POST['job_title'] ?? '');
$relationship = trim($_POST['relationship_to_head'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

if ($familyId <= 0 || $memberName === '') {
    $_SESSION['error_message'] = 'Family and member name are required.';
    $_SESSION['error_details'] = 'Please select a family and enter member name.';
    header('Location: error_page.php');
    exit;
}

if ($age !== null && ($age < 0 || $age > 120)) {
    $_SESSION['error_message'] = 'Invalid age value.';
    $_SESSION['error_details'] = 'Age should be between 0 and 120.';
    header('Location: error_page.php');
    exit;
}

try {
    $checkFamily = $pdo->prepare('SELECT id FROM families WHERE id = ? LIMIT 1');
    $checkFamily->execute([$familyId]);
    if (!$checkFamily->fetch()) {
        throw new RuntimeException('Selected family does not exist.');
    }

    $stmt = $pdo->prepare('INSERT INTO family_members (family_id, member_name, age, date_of_birth, job_title, relationship_to_head, phone, email, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $familyId,
        $memberName,
        $age,
        $dateOfBirth !== '' ? $dateOfBirth : null,
        $jobTitle !== '' ? $jobTitle : null,
        $relationship !== '' ? $relationship : null,
        $phone !== '' ? $phone : null,
        $email !== '' ? $email : null,
        $remarks !== '' ? $remarks : null
    ]);

    header('Location: member_new.php?success=1&family_id=' . $familyId);
    exit;
} catch (Throwable $e) {
    error_log('member_save failed: ' . $e->getMessage());

    $_SESSION['error_message'] = 'Failed to save member.';
    $_SESSION['error_details'] = 'Please verify details and try again.';
    header('Location: error_page.php');
    exit;
}
