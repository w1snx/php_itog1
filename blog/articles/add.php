<?php
require __DIR__ . '/../includes/header.php';

$user = currentUser($pdo);
if (!$user) redirect('auth/login.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '') $errors[] = 'Введите заголовок';
    if ($content === '') $errors[] = 'Введите текст статьи';

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $title, $content]);
        redirect('index.php');
    }
}
?>

<div class="form-card" style="max-width:700px; margin:20px auto;">
    <h2 style="text-align:center;">Создание статьи</h2>

    <?php foreach ($errors as $err): ?>
        <p style="color:red; text-align:center"><?= e($err) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">

        <div class="form-row">
            <input type="text" name="title" placeholder="Заголовок статьи" value="<?= e($_POST['title'] ?? '') ?>">
        </div>

        <div class="form-row">
            <textarea name="content" rows="8" placeholder="Текст статьи"><?= e($_POST['content'] ?? '') ?></textarea>
        </div>

        <div style="text-align:center;">
            <button class="btn btn-primary">Опубликовать</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
