<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$user = currentUser($pdo);
if (!$user) redirect('auth/login.php');

check_csrf();

$article_id = (int)$_POST['article_id'];
$parent_id = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
$content = trim($_POST['content']);

if ($article_id && $content) {
    $stmt = $pdo->prepare("
        INSERT INTO comments (article_id, user_id, parent_id, content)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$article_id, $user['id'], $parent_id, $content]);
}

redirect('article.php?id=' . $article_id);
