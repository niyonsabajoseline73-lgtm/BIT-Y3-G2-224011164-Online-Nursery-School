<?php

class ParentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, COUNT(s.id) AS pupil_count
             FROM parents p
             LEFT JOIN pupils s ON s.parent_id = p.id
             GROUP BY p.id
             ORDER BY p.full_name ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM parents WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $parent = $stmt->fetch();
        return $parent ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO parents (full_name, phone, email, address)
             VALUES (:full_name, :phone, :email, :address)'
        );

        $stmt->execute([
            'full_name' => trim($data['full_name']),
            'phone' => trim($data['phone']),
            'email' => trim($data['email']),
            'address' => trim($data['address']),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE parents
             SET full_name = :full_name, phone = :phone, email = :email, address = :address
             WHERE id = :id'
        );

        return $stmt->execute([
            'full_name' => trim($data['full_name']),
            'phone' => trim($data['phone']),
            'email' => trim($data['email']),
            'address' => trim($data['address']),
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM parents WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM parents');
        return (int) $stmt->fetch()['total'];
    }
}
