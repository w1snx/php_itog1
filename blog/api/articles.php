<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5; 
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT a.*, u.username, u.avatar,
    COALESCE((SELECT SUM(value) FROM article_votes v WHERE v.article_id = a.id),0) as rating
    FROM articles a
    JOIN users u ON u.id = a.user_id
";

$params = [];
if ($q !== '') {
    $sql .= " WHERE a.title LIKE ?";
    $params[] = "%$q%";
}

$sql .= " ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

echo json_encode([
    'page' => $page,
    'per_page' => $perPage,
    'query' => $q,
    'articles' => $articles
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
