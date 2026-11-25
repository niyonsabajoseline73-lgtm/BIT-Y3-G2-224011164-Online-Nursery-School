<?php

class DashboardController
{
    public static function index(PDO $db): array
    {
        $pupilModel = new PupilModel($db);
        $parentModel = new ParentModel($db);
        $classModel = new ClassModel($db);
        $feeModel = new FeeModel($db);

        $stats = [
            'total_pupils' => $pupilModel->count(),
            'total_parents' => $parentModel->count(),
            'total_classes' => $classModel->count(),
            'fees_collected' => $feeModel->totalCollected(),
            'total_outstanding' => $feeModel->totalOutstanding(),
        ];

        $upcomingFees = array_values(array_filter($feeModel->all(), function ($fee) {
            return $fee['payment_status'] !== 'Paid';
        }));
        $recentPupils = $pupilModel->all('', 0);
        $recentPupils = array_slice($recentPupils, 0, 5);

        return [
            'stats' => $stats,
            'upcomingFees' => array_slice($upcomingFees, 0, 5),
            'recentPupils' => $recentPupils,
        ];
    }

    public static function reports(PDO $db): array
    {
        $pupilModel = new PupilModel($db);
        $parentModel = new ParentModel($db);
        $classModel = new ClassModel($db);
        $feeModel = new FeeModel($db);

        $summary = [
            'total_pupils' => $pupilModel->count(),
            'total_parents' => $parentModel->count(),
            'total_classes' => $classModel->count(),
            'total_fees_collected' => $feeModel->totalCollected(),
            'total_outstanding_fees' => $feeModel->totalOutstanding(),
        ];

        $paymentSummary = $feeModel->paymentSummary();

        return [
            'summary' => $summary,
            'paymentSummary' => $paymentSummary,
        ];
    }
}
