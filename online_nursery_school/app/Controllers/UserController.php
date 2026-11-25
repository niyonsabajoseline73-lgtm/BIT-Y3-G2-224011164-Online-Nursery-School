<?php

class UserController
{
    public static function index(PDO $db): array
    {
        requireAdmin();
        $userModel = new UserModel($db);
        $users = $userModel->all();
        $parents = (new ParentModel($db))->all();

        return ['users' => $users, 'parents' => $parents];
    }

    public static function save(PDO $db): void
    {
        requireAdmin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('users', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = trim($_POST['role'] ?? 'staff');
        $parentId = (int) ($_POST['parent_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $fullName === '') {
            redirect('users', 'Enter a username, full name, and valid email address.', 'error');
        }
        if (!in_array($role, ['admin', 'staff', 'parent'], true)) {
            redirect('users', 'Select a valid user role.', 'error');
        }
        if ($role === 'parent' && $parentId <= 0) {
            redirect('users', 'Choose the parent record linked to this account.', 'error');
        }
        if ($role !== 'parent') {
            $parentId = 0;
        }

        $userModel = new UserModel($db);

        if ($id > 0) {
            if ($password !== '' && strlen($password) < 8) {
                redirect('users', 'Passwords must be at least 8 characters long.', 'error');
            }
            if ($id === (int) (currentUser()['id'] ?? 0) && $role !== 'admin') {
                redirect('users', 'You cannot remove administrator access from the account you are using.', 'error');
            }

            $data = [
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role,
                'parent_id' => $parentId > 0 ? $parentId : null,
            ];

            if ($password !== '') {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $userModel->update($id, $data);
            redirect('users', 'User updated successfully.', 'success');
        }

        if ($password === '') {
            redirect('users', 'A password is required for new users.', 'error');
        }
        if (strlen($password) < 8) {
            redirect('users', 'Passwords must be at least 8 characters long.', 'error');
        }

        $userModel->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'role' => $role,
            'parent_id' => $parentId > 0 ? $parentId : null,
        ]);

        redirect('users', 'User added successfully.', 'success');
    }

    public static function delete(PDO $db): void
    {
        requireAdmin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('users', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('users', 'Invalid user selected.', 'error');
        }

        if ($id === (int) (currentUser()['id'] ?? 0)) {
            redirect('users', 'You cannot delete the account you are currently using.', 'error');
        }

        $userModel = new UserModel($db);
        $userModel->delete($id);
        redirect('users', 'User deleted successfully.', 'success');
    }
}
