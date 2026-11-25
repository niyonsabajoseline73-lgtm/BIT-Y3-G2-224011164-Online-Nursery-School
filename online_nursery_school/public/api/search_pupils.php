<?php
require __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../app/helpers.php';
require __DIR__ . '/../../app/Database.php';
require __DIR__ . '/../../app/Models/PupilModel.php';

header('Content-Type: application/json');
startSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

if (!in_array(currentUser()['role'] ?? '', ['admin', 'staff'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

$pdo = Database::getConnection();
$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode([]);
    exit;
}

$pupilModel = new PupilModel($pdo);
$result = $pupilModel->search($query);

echo json_encode($result);
