<?php
require __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        redirect('');
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>

<h2>Вход</h2>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <button>Войти</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
