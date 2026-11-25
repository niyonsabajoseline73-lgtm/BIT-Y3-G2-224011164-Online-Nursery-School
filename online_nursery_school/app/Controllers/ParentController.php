<?php

class ParentController
{
    public static function portal(PDO $db): array
    {
        $user = currentUser();
        if (!$user || $user['role'] !== 'parent' || empty($user['parent_id'])) {
            redirect('dashboard', 'Parent portal access is not available for this account.', 'error');
        }

        $parentModel = new ParentModel($db);
        $parent = $parentModel->findById((int) $user['parent_id']);
        if (!$parent) {
            redirect('login', 'The parent record for this account no longer exists.', 'error');
        }

        return [
            'parent' => $parent,
            'pupils' => (new PupilModel($db))->forParent((int) $user['parent_id']),
        ];
    }

    public static function index(PDO $db): array
    {
        $parentModel = new ParentModel($db);
        $search = trim($_GET['search'] ?? '');

        $parents = $parentModel->all();
        if ($search !== '') {
            $parents = array_values(array_filter($parents, function ($parent) use ($search) {
                return stripos($parent['full_name'], $search) !== false
                    || stripos($parent['email'], $search) !== false
                    || stripos($parent['phone'], $search) !== false;
            }));
        }

        return ['parents' => $parents, 'search' => $search];
    }

    public static function save(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('parents', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($fullName === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $address === '') {
            redirect('parents', 'Enter a name, phone, valid email address, and address.', 'error');
        }

        $parentModel = new ParentModel($db);

        if ($id > 0) {
            $parentModel->update($id, [
                'full_name' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
            ]);
            redirect('parents', 'Parent updated successfully.', 'success');
        }

        $parentModel->create([
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
        ]);

        redirect('parents', 'Parent added successfully.', 'success');
    }

    public static function delete(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('parents', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('parents', 'Invalid parent selected.', 'error');
        }

        $parentModel = new ParentModel($db);
        $parentModel->delete($id);
        redirect('parents', 'Parent deleted successfully.', 'success');
    }
}
