<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$user = currentUser($pdo);
if (!$user) redirect('../auth/login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id=?");
    $stmt->execute([$id]);
    $comment = $stmt->fetch();

    if ($comment && $comment['user_id'] == $user['id']) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id=?");
        $stmt->execute([$id]);
    }

    header('Location: ../article.php?id=' . $comment['article_id']);
    exit;
}

redirect('../index.php');
