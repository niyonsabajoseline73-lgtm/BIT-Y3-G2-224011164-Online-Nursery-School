<?php

class AuthController
{
    public static function login(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('login', 'Security validation failed. Please try again.', 'error');
        }

        $identifier = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            redirect('login', 'Username/email and password are required.', 'error');
        }

        $userModel = new UserModel($db);
        $user = $userModel->findByUsernameOrEmail($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            redirect('login', 'Invalid username/email or password.', 'error');
        }

        if ($user['role'] === 'parent' && empty($user['parent_id'])) {
            redirect('login', 'This parent account is not linked to a parent record. Please contact the school administrator.', 'error');
        }

        startSession();
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'parent_id' => $user['parent_id'] === null ? null : (int) $user['parent_id'],
        ];
        session_regenerate_id(true);

        redirect($user['role'] === 'parent' ? 'family' : 'dashboard', 'Welcome back, ' . $user['full_name'] . '!', 'success');
    }

    public static function logout(): void
    {
        startSession();
        $_SESSION = [];
        session_destroy();
        redirect('login', 'You have been logged out successfully.', 'success');
    }
}
