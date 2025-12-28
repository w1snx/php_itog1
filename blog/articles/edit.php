<?php
require_once __DIR__ . '/../includes/header.php';

$user = currentUser($pdo);
if (!$user) redirect('auth/login.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article || $article['user_id'] != $user['id']) redirect('index.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') $errors[] = 'Введите заголовок';
    if ($content === '') $errors[] = 'Введите текст статьи';

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $id]);
        redirect('article.php?id=' . $id);
    }
}
?>

<div class="page-content">
    <div class="form-card">
        <h2>Редактировать статью</h2>

        <?php foreach ($errors as $err): ?>
            <p style="color:red"><?= e($err) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= csrf() ?>">

            <div class="form-row">
                <input type="text" name="title" placeholder="Заголовок" value="<?= e($_POST['title'] ?? $article['title']) ?>">
            </div>

            <div class="form-row">
                <textarea name="content" rows="10" placeholder="Текст статьи"><?= e($_POST['content'] ?? $article['content']) ?></textarea>
            </div>

            <div class="form-row">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a href="<?= BASE_URL ?>article.php?id=<?= $article['id'] ?>" class="btn btn-ghost">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
