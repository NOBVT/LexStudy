<?php

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfIsValid(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function consumeFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function currentUser(): ?array
{
    static $user = false;

    if ($user !== false) {
        return $user;
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $user = null;
        return null;
    }

    $db = tryDB();
    if (!$db || !dbSchemaIsReady()) {
        $user = null;
        return null;
    }

    $stmt = $db->prepare('SELECT id, name, email, xp, streak, last_activity, created_at FROM users WHERE id = ?');
    $stmt->execute([(int)$userId]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function requireAuthUser(): ?array
{
    return currentUser();
}

function updateDailyStreak(int $userId): void
{
    $db = tryDB();
    if (!$db) {
        return;
    }

    $stmt = $db->prepare('SELECT last_activity, streak FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        return;
    }

    $today = date('Y-m-d');
    if ($user['last_activity'] === $today) {
        return;
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $newStreak = $user['last_activity'] === $yesterday ? ((int)$user['streak'] + 1) : 1;

    $db->prepare('UPDATE users SET streak = ?, last_activity = ?, xp = xp + ? WHERE id = ?')
        ->execute([$newStreak, $today, XP_LOGIN_DAILY, $userId]);

    writeActivityLog($userId, 'login', 'Login diário', XP_LOGIN_DAILY);
}

function writeActivityLog(int $userId, string $type, string $description, int $xp): void
{
    $db = tryDB();
    if (!$db) {
        return;
    }

    try {
        $db->prepare('INSERT INTO activity_log (user_id, type, description, xp_earned) VALUES (?, ?, ?, ?)')
            ->execute([$userId, $type, $description, $xp]);
    } catch (Throwable) {
        // O registo de atividade não deve bloquear a experiência de estudo.
    }
}

function handleAuthRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['auth_action'])) {
        return;
    }

    if (!csrfIsValid($_POST['csrf_token'] ?? null)) {
        setFlash('Sessão expirada. Tenta novamente.', 'error');
        header('Location: index.php');
        exit;
    }

    $action = (string)$_POST['auth_action'];
    $db = tryDB();

    if ($action === 'logout') {
        unset($_SESSION['user_id']);
        setFlash('Sessão terminada.');
        header('Location: index.php');
        exit;
    }

    if (!$db || !dbSchemaIsReady()) {
        setFlash('A base de dados ainda não está disponível. Importa o ficheiro lexstudy.sql no phpMyAdmin.', 'error');
        header('Location: index.php');
        exit;
    }

    if ($action === 'register') {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string)($_POST['email'] ?? '')), 'UTF-8');
        $password = (string)($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password, 'UTF-8') < 6) {
            setFlash('Registo inválido. Usa nome, email válido e password com pelo menos 6 caracteres.', 'error');
            header('Location: index.php');
            exit;
        }

        try {
            $stmt = $db->prepare('INSERT INTO users (name, email, password, avatar, xp, streak, last_activity) VALUES (?, ?, ?, ?, 0, 0, NULL)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'LS']);
            $_SESSION['user_id'] = (int)$db->lastInsertId();
            updateDailyStreak((int)$_SESSION['user_id']);
            setFlash('Conta criada. O progresso passa a ficar guardado.');
        } catch (PDOException $e) {
            $message = str_contains($e->getMessage(), 'Duplicate') ? 'Esse email já está registado.' : 'Não foi possível criar a conta.';
            setFlash($message, 'error');
        }

        header('Location: index.php');
        exit;
    }

    if ($action === 'login') {
        $email = mb_strtolower(trim((string)($_POST['email'] ?? '')), 'UTF-8');
        $password = (string)($_POST['password'] ?? '');
        $stmt = $db->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            setFlash('Email ou password incorretos.', 'error');
            header('Location: index.php');
            exit;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        updateDailyStreak((int)$user['id']);
        setFlash('Sessão iniciada.');
        header('Location: index.php');
        exit;
    }
}
