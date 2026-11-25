<?php

class PupilModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(string $search = '', int $classId = 0): array
    {
        $sql = 'SELECT p.id, p.full_name, p.date_of_birth, p.class_id, p.parent_id,
                       c.class_name, pr.full_name AS parent_name
                FROM pupils p
                LEFT JOIN classes c ON c.id = p.class_id
                LEFT JOIN parents pr ON pr.id = p.parent_id
                WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.full_name LIKE :search_name OR pr.full_name LIKE :search_parent OR c.class_name LIKE :search_class)';
            $params['search_name'] = '%' . $search . '%';
            $params['search_parent'] = '%' . $search . '%';
            $params['search_class'] = '%' . $search . '%';
        }

        if ($classId > 0) {
            $sql .= ' AND p.class_id = :class_id';
            $params['class_id'] = $classId;
        }

        $sql .= ' ORDER BY p.full_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.class_name, pr.full_name AS parent_name
             FROM pupils p
             LEFT JOIN classes c ON c.id = p.class_id
             LEFT JOIN parents pr ON pr.id = p.parent_id
             WHERE p.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $pupil = $stmt->fetch();
        return $pupil ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pupils (full_name, date_of_birth, class_id, parent_id)
             VALUES (:full_name, :date_of_birth, :class_id, :parent_id)'
        );

        $stmt->execute([
            'full_name' => trim($data['full_name']),
            'date_of_birth' => $data['date_of_birth'],
            'class_id' => (int) $data['class_id'],
            'parent_id' => (int) $data['parent_id'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pupils
             SET full_name = :full_name, date_of_birth = :date_of_birth, class_id = :class_id, parent_id = :parent_id
             WHERE id = :id'
        );

        return $stmt->execute([
            'full_name' => trim($data['full_name']),
            'date_of_birth' => $data['date_of_birth'],
            'class_id' => (int) $data['class_id'],
            'parent_id' => (int) $data['parent_id'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pupils WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM pupils');
        return (int) $stmt->fetch()['total'];
    }

    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.full_name, c.class_name, pr.full_name AS parent_name
             FROM pupils p
             LEFT JOIN classes c ON c.id = p.class_id
             LEFT JOIN parents pr ON pr.id = p.parent_id
             WHERE p.full_name LIKE :query_name OR pr.full_name LIKE :query_parent OR c.class_name LIKE :query_class
             ORDER BY p.full_name ASC
             LIMIT 8'
        );

        $escapedQuery = '%' . trim($query) . '%';
        $stmt->execute([
            'query_name' => $escapedQuery,
            'query_parent' => $escapedQuery,
            'query_class' => $escapedQuery,
        ]);
        return $stmt->fetchAll();
    }

    public function forParent(int $parentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.full_name, p.date_of_birth, c.class_name,
                    COALESCE(f.amount_due, 0) AS amount_due,
                    COALESCE(f.amount_paid, 0) AS amount_paid,
                    COALESCE(f.balance, 0) AS balance,
                    f.payment_status, f.term
             FROM pupils p
             LEFT JOIN classes c ON c.id = p.class_id
             LEFT JOIN (
                 SELECT pupil_id, SUM(amount_due) AS amount_due, SUM(amount_paid) AS amount_paid,
                        SUM(balance) AS balance,
                        CASE WHEN SUM(balance) = 0 THEN "Paid"
                             WHEN SUM(amount_paid) > 0 THEN "Partial"
                             ELSE "Pending" END AS payment_status,
                        GROUP_CONCAT(DISTINCT term ORDER BY term SEPARATOR ", ") AS term
                 FROM fees
                 GROUP BY pupil_id
             ) f ON f.pupil_id = p.id
             WHERE p.parent_id = :parent_id
             ORDER BY p.full_name ASC'
        );
        $stmt->execute(['parent_id' => $parentId]);

        return $stmt->fetchAll();
    }
}
