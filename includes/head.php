<?php
$pageTitle = $pageTitle ?? 'CCMC Accounts';
$includeAppJs = $includeAppJs ?? true;
$cssFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'styles.css';
$css = is_file($cssFile) ? file_get_contents($cssFile) : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <style><?= $css ?></style>
</head>
