<?php

class ClassModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, COUNT(p.id) AS pupil_count
             FROM classes c
             LEFT JOIN pupils p ON p.class_id = c.id
             GROUP BY c.id
             ORDER BY c.class_name ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM classes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $class = $stmt->fetch();
        return $class ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO classes (class_name, teacher_name, description)
             VALUES (:class_name, :teacher_name, :description)'
        );

        $stmt->execute([
            'class_name' => trim($data['class_name']),
            'teacher_name' => trim($data['teacher_name']),
            'description' => trim($data['description']),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE classes
             SET class_name = :class_name, teacher_name = :teacher_name, description = :description
             WHERE id = :id'
        );

        return $stmt->execute([
            'class_name' => trim($data['class_name']),
            'teacher_name' => trim($data['teacher_name']),
            'description' => trim($data['description']),
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM classes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM classes');
        return (int) $stmt->fetch()['total'];
    }
}
