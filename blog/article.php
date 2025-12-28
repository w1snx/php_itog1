<?php
require __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

/* Статья */
$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.avatar
    FROM articles a
    JOIN users u ON u.id = a.user_id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) redirect('index.php');

/* Рейтинг */
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(value), 0)
    FROM article_votes
    WHERE article_id = ?
");
$stmt->execute([$article['id']]);
$rating = (int)$stmt->fetchColumn();

$userVote = 0;
if ($user) {
    $stmt = $pdo->prepare("
        SELECT value FROM article_votes
        WHERE article_id = ? AND user_id = ?
    ");
    $stmt->execute([$article['id'], $user['id']]);
    $userVote = (int)($stmt->fetchColumn() ?? 0);
}

/* Комментарии */
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.avatar
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.article_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$article['id']]);
$raw = $stmt->fetchAll();

$comments = [];
foreach ($raw as $c) {
    $pid = $c['parent_id'] ?? 0;
    $comments[$pid][] = $c;
}

function render_comments($parent, $comments, $user, $level = 0) {
    if (!isset($comments[$parent]) || $level > 3) return;
    echo '<ul class="comments">';
    foreach ($comments[$parent] as $c) {
        echo '<li>';
        echo '<b>' . e($c['username']) . '</b>';
        echo ' <small>(' . $c['created_at'] . ')</small><br>';
        echo nl2br(e($c['content'])) . '<br>';
        echo '<a href="#comment-form" onclick="document.getElementById(\'parent_id\').value=' . $c['id'] . '">Ответить</a>';

        if ($user && $user['id'] == $c['user_id']) {
            echo ' | <a href="' . BASE_URL . 'comments/delete.php?id=' . $c['id'] . '">Удалить</a>';
        }

        render_comments($c['id'], $comments, $user, $level + 1);
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div style="max-width:900px;margin:0 auto;">

    <h1 class="article-title"><?= e($article['title']) ?></h1>
    <div class="article-author">
        Автор: <?= e($article['username']) ?> • <?= $article['created_at'] ?>
    </div>

    <?php if ($user && $user['id'] == $article['user_id']): ?>
        <p>
            <a class="btn btn-ghost btn-small" href="<?= BASE_URL ?>articles/edit.php?id=<?= $article['id'] ?>">Редактировать</a>
            <a class="btn btn-ghost btn-small" href="<?= BASE_URL ?>articles/delete.php?id=<?= $article['id'] ?>">Удалить</a>
        </p>
    <?php endif; ?>

    <div><?= nl2br(e($article['content'])) ?></div>

    <hr>

    <div class="rating">
        <strong>Рейтинг:</strong> <?= $rating ?>
        <?php if ($user): ?>
            <a href="<?= BASE_URL ?>articles/vote.php?id=<?= $article['id'] ?>&v=up"
               style="color:<?= $userVote === 1 ? 'green' : '#fff' ?>">👍</a>
            <a href="<?= BASE_URL ?>articles/vote.php?id=<?= $article['id'] ?>&v=down"
               style="color:<?= $userVote === -1 ? 'red' : '#fff' ?>">👎</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php">Войдите, чтобы голосовать</a>
        <?php endif; ?>
    </div>

    <hr>

    <h3>Комментарии</h3>
    <?php render_comments(0, $comments, $user); ?>

    <?php if ($user): ?>
        <div class="form-card" id="comment-form">
            <h3 style="text-align:center;">Комментарий</h3>

            <form method="post" action="<?= BASE_URL ?>comments/add.php">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                <input type="hidden" name="parent_id" id="parent_id">

                <div class="form-row">
                    <textarea name="content" rows="4" placeholder="Ваш комментарий..." required></textarea>
                </div>

                <div style="text-align:center;">
                    <button class="btn btn-primary btn-small">Отправить</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <p style="text-align:center;">
            <a class="btn btn-ghost btn-small" href="<?= BASE_URL ?>auth/login.php">
                Войдите, чтобы комментировать
            </a>
        </p>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
