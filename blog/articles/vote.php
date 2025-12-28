<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$user = currentUser($pdo);
if (!$user) {
    redirect('auth/login.php');
}

$article_id = (int)($_GET['id'] ?? 0);
$vote = $_GET['v'] ?? '';

if ($article_id <= 0 || !in_array($vote, ['up', 'down'], true)) {
    redirect('index.php');
}

$value = $vote === 'up' ? 1 : -1;

$stmt = $pdo->prepare("
    INSERT INTO article_votes (user_id, article_id, value)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE value = VALUES(value)
");
$stmt->execute([$user['id'], $article_id, $value]);

redirect('article.php?id=' . $article_id);
