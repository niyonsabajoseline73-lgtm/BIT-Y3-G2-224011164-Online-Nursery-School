<?php

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT u.id, u.username, u.email, u.full_name, u.role, u.parent_id,
                    p.full_name AS parent_name, u.created_at
             FROM users u
             LEFT JOIN parents p ON p.id = u.parent_id
             ORDER BY u.full_name ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1'
        );
        $stmt->execute([
            'username' => $identifier,
            'email' => $identifier,
        ]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, full_name, role, parent_id)
             VALUES (:username, :email, :password_hash, :full_name, :role, :parent_id)'
        );

        $stmt->execute([
            'username' => trim($data['username']),
            'email' => trim($data['email']),
            'password_hash' => $data['password_hash'],
            'full_name' => trim($data['full_name']),
            'role' => $data['role'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'username' => trim($data['username']),
            'email' => trim($data['email']),
            'full_name' => trim($data['full_name']),
            'role' => $data['role'],
            'parent_id' => $data['parent_id'] ?? null,
        ];

        if (!empty($data['password_hash'])) {
            $fields['password_hash'] = $data['password_hash'];
        }

        $set = [];
        foreach ($fields as $key => $value) {
            $set[] = $key . ' = :' . $key;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        foreach ($fields as $key => $value) {
            $type = $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $stmt->bindValue(':' . $key, $value, $type);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM users');
        return (int) $stmt->fetch()['total'];
    }
}
