<?php
require __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];

if ($q !== '') {
    $where = 'WHERE a.title LIKE ?';
    $params[] = '%' . $q . '%';
}

/* Количество */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM articles a
    $where
");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, ceil($total / $perPage));

/* Статьи */
$stmt = $pdo->prepare("
    SELECT a.*, u.username
    FROM articles a
    JOIN users u ON u.id = a.user_id
    $where
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>

<div class="search-box">
    <form method="get" style="display:flex;width:100%;">
        <input type="text" name="q" placeholder="Поиск по заголовку..." value="<?= e($q) ?>">
        <button class="btn btn-primary btn-small">Найти</button>
    </form>
</div>

<?php if ($user): ?>
    <p style="text-align:center;">
        <a class="btn btn-primary btn-small" href="<?= BASE_URL ?>articles/add.php">
            + Создать статью
        </a>
    </p>
<?php endif; ?>

<?php foreach ($articles as $a): ?>
    <div class="form-card" style="max-width:800px;">
        <h2 class="article-title">
            <a href="<?= BASE_URL ?>article.php?id=<?= $a['id'] ?>">
                <?= e($a['title']) ?>
            </a>
        </h2>
        <div class="article-author">
            <?= e($a['username']) ?> • <?= $a['created_at'] ?>
        </div>
    </div>
<?php endforeach; ?>

<div style="text-align:center;margin-top:20px;">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="btn btn-ghost btn-small"
           href="?page=<?= $i ?>&q=<?= urlencode($q) ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
