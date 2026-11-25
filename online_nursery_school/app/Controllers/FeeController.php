<?php

class FeeController
{
    public static function index(PDO $db): array
    {
        $feeModel = new FeeModel($db);
        $term = trim($_GET['term'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $fees = $feeModel->all($term, $status);
        $terms = ['Term 1', 'Term 2', 'Term 3'];

        return ['fees' => $fees, 'term' => $term, 'status' => $status, 'terms' => $terms];
    }

    public static function save(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('fees', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $pupilId = (int) ($_POST['pupil_id'] ?? 0);
        $term = trim($_POST['term'] ?? '');
        $amountDueInput = trim($_POST['amount_due'] ?? '');
        $amountPaidInput = trim($_POST['amount_paid'] ?? '0');

        $validMoney = '/^\d+(?:\.\d{1,2})?$/';
        if ($pupilId <= 0 || !in_array($term, ['Term 1', 'Term 2', 'Term 3'], true)
            || !preg_match($validMoney, $amountDueInput) || !preg_match($validMoney, $amountPaidInput)) {
            redirect('fees', 'Choose a pupil and term, and enter valid fee amounts.', 'error');
        }

        $amountDue = (float) $amountDueInput;
        $amountPaid = (float) $amountPaidInput;
        if ($amountDue <= 0 || $amountPaid < 0 || $amountPaid > $amountDue) {
            redirect('fees', 'The amount due must be positive, and amount paid must be between zero and the amount due.', 'error');
        }

        $feeModel = new FeeModel($db);

        if ($id > 0) {
            $feeModel->update($id, [
                'pupil_id' => $pupilId,
                'term' => $term,
                'amount_due' => $amountDue,
                'amount_paid' => $amountPaid,
            ]);
            redirect('fees', 'Fee record updated successfully.', 'success');
        }

        $feeModel->create([
            'pupil_id' => $pupilId,
            'term' => $term,
            'amount_due' => $amountDue,
            'amount_paid' => $amountPaid,
        ]);

        redirect('fees', 'Fee record added successfully.', 'success');
    }

    public static function delete(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('fees', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('fees', 'Invalid fee selected.', 'error');
        }

        $feeModel = new FeeModel($db);
        $feeModel->delete($id);
        redirect('fees', 'Fee record deleted successfully.', 'success');
    }
}
