<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$user = currentUser($pdo);
if (!$user) redirect('auth/login.php');

$errors = [];
$success = null;

if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    if (!empty($user['avatar'])) {
        delete_avatar_file($user['avatar']);
        $stmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
    }
    redirect('auth/profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $res = handle_avatar_upload($_FILES['avatar'] ?? [], $user['id'], $pdo);
    if ($res['success']) {
        $success = $res['message'];
        $user['avatar'] = $res['filename'];
        redirect('auth/profile.php');
    } else {
        $errors[] = $res['message'];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<h2>Профиль — <?= e($user['username']) ?></h2>

<?php if ($success): ?>
    <p style="color:green"><?= e($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
    <p style="color:red"><?= e($err) ?></p>
<?php endforeach; ?>

<p>Текущий аватар:</p>
<img src="<?= avatar_url($user['avatar']) ?>" width="120" style="border-radius:8px;border:1px solid #ddd"><br><br>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <input type="file" name="avatar" accept="image/*" required><br><br>
    <button type="submit">Загрузить аватар</button>
</form>

<p><a href="<?= BASE_URL ?>auth/profile.php?delete=1" onclick="return confirm('Удалить аватар?')">Удалить аватар</a></p>

<p><a href="<?= BASE_URL ?>index.php">← Назад</a></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
