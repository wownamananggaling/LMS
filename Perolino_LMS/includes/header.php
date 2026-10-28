<?php
require_once __DIR__ . '/functions.php';
$current = basename($_SERVER['PHP_SELF']);
function nav($file, $label, $current) {
    echo '<a href="' . $file . '"' . ($current === $file ? ' class="active"' : '') . '>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Library Management System') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1>Library Management System</h1>
    <nav>
        <?php nav('index.php', 'Dashboard', $current);
              nav('student.php', 'Student', $current);
              nav('book.php', 'Book', $current);
              nav('borrow.php', 'Borrow', $current); ?>
    </nav>
</header>
<main>
<?php show_flash(); ?>
