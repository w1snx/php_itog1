<?php
require __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $error = 'Пароль минимум 6 символов';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        ]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        redirect('');
    }
}
?>

<h2>Регистрация</h2>
<?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

<form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <input name="username" placeholder="Имя" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <button>Зарегистрироваться</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
