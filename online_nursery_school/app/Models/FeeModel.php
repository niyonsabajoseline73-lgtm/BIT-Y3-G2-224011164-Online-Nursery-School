<?php

class FeeModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(string $term = '', string $status = ''): array
    {
        $sql = 'SELECT f.*, p.full_name AS pupil_name, c.class_name
                FROM fees f
                INNER JOIN pupils p ON p.id = f.pupil_id
                LEFT JOIN classes c ON c.id = p.class_id
                WHERE 1 = 1';

        $params = [];

        if ($term !== '') {
            $sql .= ' AND f.term = :term';
            $params['term'] = $term;
        }

        if ($status !== '') {
            $sql .= ' AND f.payment_status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY f.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT f.*, p.full_name AS pupil_name
             FROM fees f
             INNER JOIN pupils p ON p.id = f.pupil_id
             WHERE f.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $fee = $stmt->fetch();
        return $fee ?: null;
    }

    public function create(array $data): int
    {
        [$amountDue, $amountPaid] = $this->validatedAmounts($data['amount_due'], $data['amount_paid']);
        $balance = $amountDue - $amountPaid;
        $status = $this->calculateStatus($amountDue, $amountPaid);

        $stmt = $this->db->prepare(
            'INSERT INTO fees (pupil_id, term, amount_due, amount_paid, balance, payment_status)
             VALUES (:pupil_id, :term, :amount_due, :amount_paid, :balance, :payment_status)'
        );

        $stmt->execute([
            'pupil_id' => (int) $data['pupil_id'],
            'term' => trim($data['term']),
            'amount_due' => $amountDue,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'payment_status' => $status,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        [$amountDue, $amountPaid] = $this->validatedAmounts($data['amount_due'], $data['amount_paid']);
        $balance = $amountDue - $amountPaid;
        $status = $this->calculateStatus($amountDue, $amountPaid);

        $stmt = $this->db->prepare(
            'UPDATE fees
             SET pupil_id = :pupil_id, term = :term, amount_due = :amount_due, amount_paid = :amount_paid,
                 balance = :balance, payment_status = :payment_status
             WHERE id = :id'
        );

        return $stmt->execute([
            'pupil_id' => (int) $data['pupil_id'],
            'term' => trim($data['term']),
            'amount_due' => $amountDue,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'payment_status' => $status,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM fees WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function calculateStatus(float $amountDue, float $amountPaid): string
    {
        if ($amountPaid >= $amountDue) {
            return 'Paid';
        }

        if ($amountPaid > 0) {
            return 'Partial';
        }

        return 'Pending';
    }

    private function validatedAmounts($amountDueInput, $amountPaidInput): array
    {
        if (!is_numeric($amountDueInput) || !is_numeric($amountPaidInput)) {
            throw new InvalidArgumentException('Fee amounts must be valid numbers.');
        }

        $amountDue = round((float) $amountDueInput, 2);
        $amountPaid = round((float) $amountPaidInput, 2);
        if ($amountDue <= 0 || $amountPaid < 0 || $amountPaid > $amountDue) {
            throw new InvalidArgumentException('Amount paid must be between zero and the amount due.');
        }

        return [$amountDue, $amountPaid];
    }

    public function totalCollected(): float
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(amount_paid), 0) AS total FROM fees');
        return (float) $stmt->fetch()['total'];
    }

    public function totalOutstanding(): float
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(balance), 0) AS total FROM fees');
        return (float) $stmt->fetch()['total'];
    }

    public function paymentSummary(): array
    {
        $stmt = $this->db->query(
            'SELECT payment_status, COUNT(*) AS total
             FROM fees
             GROUP BY payment_status'
        );

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM fees');
        return (int) $stmt->fetch()['total'];
    }
}
