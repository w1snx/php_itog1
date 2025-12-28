<?php
session_start();

define('BASE_URL', 'http://localhost/blog/');

$pdo = new PDO(
    'mysql:host=localhost;dbname=blog;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
