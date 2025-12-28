<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$user = currentUser($pdo);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Блог</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/style.css">
</head>
<body>

<header>
    <a href="<?= BASE_URL ?>">Главная</a>

    <?php if ($user): ?>
        <span><?= e($user['username']) ?></span>

        <img
            src="<?= avatar_url($user['avatar']) ?>"
            alt="avatar"
            width="30"
            height="30"
            style="vertical-align:middle;border-radius:50%"
        >

        <a href="<?= BASE_URL ?>auth/profile.php">Профиль</a>
        <a href="<?= BASE_URL ?>articles/add.php">Новая статья</a>
        <a href="<?= BASE_URL ?>auth/logout.php">Выйти</a>

    <?php else: ?>
        <a href="<?= BASE_URL ?>auth/login.php">Войти</a>
        <a href="<?= BASE_URL ?>auth/register.php">Регистрация</a>
    <?php endif; ?>
</header>

<main>
