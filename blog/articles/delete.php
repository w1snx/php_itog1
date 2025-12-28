<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$user = currentUser($pdo);
if (!$user) redirect('../auth/login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id=? AND user_id=?");
    $stmt->execute([$id, $user['id']]);
}

header('Location: ../index.php');
exit;
