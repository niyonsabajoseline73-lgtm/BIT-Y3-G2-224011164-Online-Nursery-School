<?php

function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    startSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token): bool
{
    startSession();

    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flashMessage(): ?array
{
    startSession();

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function redirect($page, $message = null, $type = 'success')
{
    startSession();

    if ($message !== null) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    $location = appUrl('index.php?page=' . ltrim($page, '/'));
    header('Location: ' . $location);
    exit;
}

function isLoggedIn(): bool
{
    startSession();

    return !empty($_SESSION['user']);
}

function currentUser(): ?array
{
    startSession();

    return $_SESSION['user'] ?? null;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('login', 'Please log in to continue.', 'error');
    }
}

function requireAdmin()
{
    $user = currentUser();

    if (!$user || $user['role'] !== 'admin') {
        redirect('dashboard', 'Only administrators can manage users.', 'error');
    }
}

function shortDate($date): string
{
    if (empty($date)) {
        return 'N/A';
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }

    return date('M d, Y', $timestamp);
}

function formatCurrency($amount): string
{
    return 'RWF ' . number_format((float) $amount, 0, '.', ',');
}
