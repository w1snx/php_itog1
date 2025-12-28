<?php
require_once __DIR__ . '/config.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function currentUser($pdo) {
    if (!isset($_SESSION['user_id'])) return null;

    static $user = null;
    if ($user !== null) return $user;

    $stmt = $pdo->prepare("SELECT id, username, email, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function csrf() {
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf']) || !hash_equals(($_SESSION['csrf'] ?? ''), $_POST['csrf'])) {
            http_response_code(403);
            die('CSRF validation failed');
        }
    }
}

// ========================
// Аватары
// ========================

function avatar_url($avatarFilename, $defaultPath = 'assets/default-avatar.jpg') {
    if ($avatarFilename) {
        $uploadPath = __DIR__ . '/../uploads/avatars/' . $avatarFilename;
        if (file_exists($uploadPath) && is_file($uploadPath)) {
            return BASE_URL . 'uploads/avatars/' . $avatarFilename;
        }
    }
    return BASE_URL . $defaultPath;
}

function delete_avatar_file($avatarFilename) {
    if (!$avatarFilename) return;
    $path = __DIR__ . '/../uploads/avatars/' . $avatarFilename;
    if (is_file($path)) {
        @unlink($path);
    }
}

function handle_avatar_upload(array $file, int $userId, PDO $pdo): array {
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'Файл не выбран', 'filename' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Ошибка при загрузке файла', 'filename' => null];
    }

    $maxBytes = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'message' => 'Файл слишком большой (макс 2MB)', 'filename' => null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        return ['success' => false, 'message' => 'Неподдерживаемый формат. Разрешены: jpg, png, gif, webp', 'filename' => null];
    }

    $avatarsDir = __DIR__ . '/../uploads/avatars';
    if (!is_dir($avatarsDir)) {
        if (!mkdir($avatarsDir, 0755, true) && !is_dir($avatarsDir)) {
            return ['success' => false, 'message' => 'Не удалось создать папку для аватаров', 'filename' => null];
        }
    }

    try {
        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    } catch (Exception $e) {
        $name = uniqid('av_', true) . '.' . $allowed[$mime];
    }

    $target = $avatarsDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'message' => 'Не удалось сохранить файл', 'filename' => null];
    }

    try {
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $old = $stmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$name, $userId]);

        if ($old) {
            delete_avatar_file($old);
        }

        return ['success' => true, 'message' => 'Аватар успешно загружен', 'filename' => $name];
    } catch (PDOException $ex) {
        @unlink($target);
        return ['success' => false, 'message' => 'Ошибка базы данных: ' . $ex->getMessage(), 'filename' => null];
    }
}
