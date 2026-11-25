<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/Database.php';

require __DIR__ . '/../app/Models/UserModel.php';
require __DIR__ . '/../app/Models/ParentModel.php';
require __DIR__ . '/../app/Models/ClassModel.php';
require __DIR__ . '/../app/Models/PupilModel.php';
require __DIR__ . '/../app/Models/FeeModel.php';

require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/DashboardController.php';
require __DIR__ . '/../app/Controllers/PupilController.php';
require __DIR__ . '/../app/Controllers/ParentController.php';
require __DIR__ . '/../app/Controllers/ClassController.php';
require __DIR__ . '/../app/Controllers/FeeController.php';
require __DIR__ . '/../app/Controllers/UserController.php';

startSession();
$pdo = Database::getConnection();
$page = $_GET['page'] ?? 'login';
$flash = flashMessage();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $actionPages = [
        'login' => 'login',
        'save_pupil' => 'pupils',
        'delete_pupil' => 'pupils',
        'save_parent' => 'parents',
        'delete_parent' => 'parents',
        'save_class' => 'classes',
        'delete_class' => 'classes',
        'save_fee' => 'fees',
        'delete_fee' => 'fees',
        'save_user' => 'users',
        'delete_user' => 'users',
    ];

    if ($action !== 'login' && $action !== 'logout' && !in_array($user['role'] ?? '', ['admin', 'staff'], true)) {
        redirect('family', 'Parent accounts can only view their children and fee balances.', 'error');
    }

    try {
        switch ($action) {
            case 'login':
                AuthController::login($pdo);
                break;
            case 'logout':
                AuthController::logout();
                break;
            case 'save_pupil':
                PupilController::save($pdo);
                break;
            case 'delete_pupil':
                PupilController::delete($pdo);
                break;
            case 'save_parent':
                ParentController::save($pdo);
                break;
            case 'delete_parent':
                ParentController::delete($pdo);
                break;
            case 'save_class':
                ClassController::save($pdo);
                break;
            case 'delete_class':
                ClassController::delete($pdo);
                break;
            case 'save_fee':
                FeeController::save($pdo);
                break;
            case 'delete_fee':
                FeeController::delete($pdo);
                break;
            case 'save_user':
                UserController::save($pdo);
                break;
            case 'delete_user':
                UserController::delete($pdo);
                break;
        }
    } catch (PDOException $exception) {
        error_log('Nursery school request failed: ' . $exception->getMessage());
        $targetPage = $actionPages[$action] ?? ($page ?? 'dashboard');
        redirect($targetPage, 'This change could not be saved. Check for duplicate values or related records, then try again.', 'error');
    }
}

if ($page === 'login' && isLoggedIn()) {
    redirect(($user['role'] ?? '') === 'parent' ? 'family' : 'dashboard');
}

if (!isLoggedIn() && $page !== 'login') {
    redirect('login', 'Please log in to continue.', 'error');
}

if (isLoggedIn() && ($user['role'] ?? '') === 'parent' && $page !== 'family') {
    redirect('family');
}

if (isLoggedIn() && ($user['role'] ?? '') !== 'parent' && $page === 'family') {
    redirect('dashboard');
}

function renderDashboardPage(array $data, array $user): string
{
    $stats = $data['stats'];
    $upcomingFees = $data['upcomingFees'];
    $recentPupils = $data['recentPupils'];

    $cards = [
        ['title' => 'Total Pupils', 'value' => $stats['total_pupils'], 'icon' => '👦', 'trend' => '+12.5%'],
        ['title' => 'Total Parents', 'value' => $stats['total_parents'], 'icon' => '👨‍👩‍👧‍👦', 'trend' => '+8.4%'],
        ['title' => 'Total Classes', 'value' => $stats['total_classes'], 'icon' => '🏫', 'trend' => '+3.6%'],
        ['title' => 'Fees Collected', 'value' => formatCurrency($stats['fees_collected']), 'icon' => '💰', 'trend' => '+24.2%'],
    ];

    $feeRows = '';
    foreach ($upcomingFees as $fee) {
        $pupil = $fee['pupil_name'] ?? 'Unknown pupil';
        $feeRows .= '<tr>'
            . '<td>' . e($pupil) . '</td>'
            . '<td>' . e($fee['term']) . '</td>'
            . '<td>' . formatCurrency($fee['balance']) . '</td>'
            . '<td><span class="badge status-' . strtolower(e($fee['payment_status'])) . '">' . e($fee['payment_status']) . '</span></td>'
            . '</tr>';
    }

    $pupilRows = '';
    foreach ($recentPupils as $pupil) {
        $pupilRows .= '<tr>'
            . '<td>' . e($pupil['id']) . '</td>'
            . '<td>' . e($pupil['full_name']) . '</td>'
            . '<td>' . e($pupil['class_name'] ?? 'N/A') . '</td>'
            . '<td>' . e($pupil['parent_name'] ?? 'N/A') . '</td>'
            . '</tr>';
    }

    $html = '<section class="welcome-panel">
        <div>
            <p class="eyebrow accent">Welcome back</p>
            <h3>Hello, ' . e($user['full_name'] ?? 'Administrator') . '</h3>
            <p>Track student growth, fee collections, and daily nursery school operations in one place.</p>
            <div class="welcome-actions">
                <a href="' . appUrl('index.php?page=pupils') . '" class="btn btn-primary">Quick Add Pupil</a>
                <a href="' . appUrl('index.php?page=reports') . '" class="btn btn-secondary">View Reports</a>
            </div>
        </div>
        <div class="welcome-illustration">
            <div class="bubble bubble-one"></div>
            <div class="bubble bubble-two"></div>
            <div class="hero-figure">
                <div class="figure-head"></div>
                <div class="figure-body"></div>
            </div>
        </div>
    </section>

    <section class="stats-grid">';

    foreach ($cards as $card) {
        $html .= '<div class="stat-card">
            <div class="stat-top">
                <span class="stat-icon">' . e($card['icon']) . '</span>
                <span class="trend">' . e($card['trend']) . '</span>
            </div>
            <p>' . e($card['title']) . '</p>
            <h3>' . e($card['value']) . '</h3>
        </div>';
    }

    $html .= '</section>

    <section class="content-grid dashboard-grid">
        <div class="panel chart-panel">
            <div class="panel-header">
                <h4>Fee Collection</h4>
                <span class="panel-tag">This term</span>
            </div>
            <div class="chart-bars" aria-label="Fee collection chart">
                <div class="bar-group"><span class="bar" style="height: 52%"></span><label>Jan</label></div>
                <div class="bar-group"><span class="bar" style="height: 68%"></span><label>Feb</label></div>
                <div class="bar-group"><span class="bar" style="height: 48%"></span><label>Mar</label></div>
                <div class="bar-group"><span class="bar" style="height: 74%"></span><label>Apr</label></div>
                <div class="bar-group"><span class="bar" style="height: 88%"></span><label>May</label></div>
                <div class="bar-group"><span class="bar" style="height: 79%"></span><label>Jun</label></div>
            </div>
        </div>

        <div class="panel list-panel">
            <div class="panel-header">
                <h4>Upcoming Fee Payments</h4>
                <a href="' . appUrl('index.php?page=fees') . '" class="text-link">View all</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Pupil</th>
                        <th>Term</th>
                        <th>Still Expected</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $feeRows . '
                </tbody>
            </table>
        </div>

        <div class="panel table-panel wide-panel">
            <div class="panel-header">
                <h4>Recent Pupils</h4>
                <a href="' . appUrl('index.php?page=pupils') . '" class="text-link">See all</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Pupil ID</th>
                        <th>Full Name</th>
                        <th>Class</th>
                        <th>Parent</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $pupilRows . '
                </tbody>
            </table>
        </div>
    </section>';

    return $html;
}

function renderPupilsPage(array $data): string
{
    $classes = $data['classes'];
    $parents = $data['parents'];
    $pupils = $data['pupils'];
    $search = $data['search'];
    $classId = $data['classId'];

    $classOptions = '';
    foreach ($classes as $class) {
        $selected = (int) $class['id'] === $classId ? ' selected' : '';
        $classOptions .= '<option value="' . e($class['id']) . '"' . $selected . '>' . e($class['class_name']) . '</option>';
    }

    $parentOptions = '';
    foreach ($parents as $parent) {
        $parentOptions .= '<option value="' . e($parent['id']) . '">' . e($parent['full_name']) . '</option>';
    }

    $rows = '';
    foreach ($pupils as $pupil) {
        $rows .= '<tr>
            <td>' . e($pupil['id']) . '</td>
            <td>' . e($pupil['full_name']) . '</td>
            <td>' . e($pupil['date_of_birth']) . '</td>
            <td>' . e($pupil['class_name'] ?? 'N/A') . '</td>
            <td>' . e($pupil['parent_name'] ?? 'N/A') . '</td>
            <td>
                <div class="actions">
                    <button type="button" class="btn small btn-secondary" data-edit-pupil="' . e($pupil['id']) . '" data-name="' . e($pupil['full_name']) . '" data-dob="' . e($pupil['date_of_birth']) . '" data-class="' . e($pupil['class_id']) . '" data-parent="' . e($pupil['parent_id']) . '">Edit</button>
                    <form method="post" class="inline-form" onsubmit="return confirm(\'Are you sure you want to delete this pupil?\');">
                        <input type="hidden" name="action" value="delete_pupil">
                        <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                        <input type="hidden" name="id" value="' . e($pupil['id']) . '">
                        <button type="submit" class="btn small btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Pupil management</p>
                <h3>Pupils</h3>
            </div>
            <button type="button" class="btn btn-primary" data-open-modal="pupilModal">+ Quick Add Pupil</button>
        </div>

        <div class="toolbar-row">
            <form method="get" class="filter-form" action="' . appUrl('index.php?page=pupils') . '">
                <input type="hidden" name="page" value="pupils">
                <input type="search" name="search" value="' . e($search) . '" placeholder="Search pupils...">
                <select name="class_id">
                    <option value="0">All classes</option>
                    ' . $classOptions . '
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            <div class="live-search-box">
                <input id="liveSearch" type="search" placeholder="Live search by name, parent or class">
            </div>
        </div>

        <div class="panel table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Pupil ID</th>
                        <th>Full Name</th>
                        <th>Date of Birth</th>
                        <th>Class</th>
                        <th>Parent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pupilTableBody">
                    ' . $rows . '
                </tbody>
            </table>
        </div>
    </section>

    <div id="pupilModal" class="modal hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Add or Edit Pupil</h4>
                <button type="button" class="close-modal" data-close-modal="pupilModal">×</button>
            </div>
            <form method="post" action="' . appUrl('index.php?page=pupils') . '">
                <input type="hidden" name="action" value="save_pupil">
                <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                <input type="hidden" name="id" id="pupilId" value="0">
                <div class="form-grid two-columns">
                    <label>
                        <span>Full name</span>
                        <input type="text" name="full_name" id="pupilFullName" required>
                    </label>
                    <label>
                        <span>Date of birth</span>
                        <input type="date" name="date_of_birth" id="pupilDob" required>
                    </label>
                    <label>
                        <span>Class</span>
                        <select name="class_id" id="pupilClass" required>
                            <option value="">Select class</option>
                            ' . $classOptions . '
                        </select>
                    </label>
                    <label>
                        <span>Parent</span>
                        <select name="parent_id" id="pupilParent" required>
                            <option value="">Select parent</option>
                            ' . $parentOptions . '
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-modal="pupilModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Pupil</button>
                </div>
            </form>
        </div>
    </div>';
}

function renderParentsPage(array $data): string
{
    $parents = $data['parents'];
    $search = $data['search'];

    $rows = '';
    foreach ($parents as $parent) {
        $rows .= '<tr>
            <td>' . e($parent['id']) . '</td>
            <td>' . e($parent['full_name']) . '</td>
            <td>' . e($parent['phone']) . '</td>
            <td>' . e($parent['email']) . '</td>
            <td>' . e($parent['address']) . '</td>
            <td>' . e($parent['pupil_count'] ?? 0) . '</td>
            <td>
                <div class="actions">
                    <button type="button" class="btn small btn-secondary" data-edit-parent="' . e($parent['id']) . '" data-name="' . e($parent['full_name']) . '" data-phone="' . e($parent['phone']) . '" data-email="' . e($parent['email']) . '" data-address="' . e($parent['address']) . '">Edit</button>
                    <form method="post" class="inline-form" onsubmit="return confirm(\'Delete this parent and associated records?\');">
                        <input type="hidden" name="action" value="delete_parent">
                        <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                        <input type="hidden" name="id" value="' . e($parent['id']) . '">
                        <button type="submit" class="btn small btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Parent management</p>
                <h3>Parents</h3>
            </div>
            <button type="button" class="btn btn-primary" data-open-modal="parentModal">+ Add Parent</button>
        </div>

        <div class="toolbar-row">
            <form method="get" class="filter-form" action="' . appUrl('index.php?page=parents') . '">
                <input type="hidden" name="page" value="parents">
                <input type="search" name="search" value="' . e($search) . '" placeholder="Search parents...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="panel table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Parent ID</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Pupils</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $rows . '
                </tbody>
            </table>
        </div>
    </section>

    <div id="parentModal" class="modal hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Add or Edit Parent</h4>
                <button type="button" class="close-modal" data-close-modal="parentModal">×</button>
            </div>
            <form method="post" action="' . appUrl('index.php?page=parents') . '">
                <input type="hidden" name="action" value="save_parent">
                <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                <input type="hidden" name="id" id="parentId" value="0">
                <div class="form-grid two-columns">
                    <label>
                        <span>Full name</span>
                        <input type="text" name="full_name" id="parentName" required>
                    </label>
                    <label>
                        <span>Phone number</span>
                        <input type="tel" name="phone" id="parentPhone" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" id="parentEmail" required>
                    </label>
                    <label>
                        <span>Address</span>
                        <input type="text" name="address" id="parentAddress" required>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-modal="parentModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Parent</button>
                </div>
            </form>
        </div>
    </div>';
}

function renderClassesPage(array $data): string
{
    $classes = $data['classes'];

    $rows = '';
    foreach ($classes as $class) {
        $rows .= '<tr>
            <td>' . e($class['id']) . '</td>
            <td>' . e($class['class_name']) . '</td>
            <td>' . e($class['teacher_name']) . '</td>
            <td>' . e($class['description']) . '</td>
            <td>' . e($class['pupil_count']) . '</td>
            <td>
                <div class="actions">
                    <button type="button" class="btn small btn-secondary" data-edit-class="' . e($class['id']) . '" data-name="' . e($class['class_name']) . '" data-teacher="' . e($class['teacher_name']) . '" data-description="' . e($class['description']) . '">Edit</button>
                    <form method="post" class="inline-form" onsubmit="return confirm(\'Delete this class?\');">
                        <input type="hidden" name="action" value="delete_class">
                        <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                        <input type="hidden" name="id" value="' . e($class['id']) . '">
                        <button type="submit" class="btn small btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Class management</p>
                <h3>Classes</h3>
            </div>
            <button type="button" class="btn btn-primary" data-open-modal="classModal">+ Add Class</button>
        </div>

        <div class="panel table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Class ID</th>
                        <th>Class Name</th>
                        <th>Teacher Name</th>
                        <th>Description</th>
                        <th>Pupils</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $rows . '
                </tbody>
            </table>
        </div>
    </section>

    <div id="classModal" class="modal hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Add or Edit Class</h4>
                <button type="button" class="close-modal" data-close-modal="classModal">×</button>
            </div>
            <form method="post" action="' . appUrl('index.php?page=classes') . '">
                <input type="hidden" name="action" value="save_class">
                <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                <input type="hidden" name="id" id="classId" value="0">
                <div class="form-grid two-columns">
                    <label>
                        <span>Class name</span>
                        <input type="text" name="class_name" id="className" required>
                    </label>
                    <label>
                        <span>Teacher name</span>
                        <input type="text" name="teacher_name" id="classTeacher" required>
                    </label>
                    <label class="full-width">
                        <span>Description</span>
                        <textarea name="description" id="classDescription" rows="4"></textarea>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-modal="classModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Class</button>
                </div>
            </form>
        </div>
    </div>';
}

function renderFeesPage(array $data): string
{
    $fees = $data['fees'];
    $terms = $data['terms'];
    $term = $data['term'];
    $status = $data['status'];

    $termOptions = '';
    foreach ($terms as $option) {
        $selected = $option === $term ? ' selected' : '';
        $termOptions .= '<option value="' . e($option) . '"' . $selected . '>' . e($option) . '</option>';
    }

    $statusOptions = '<option value="">All statuses</option>
        <option value="Pending"' . ($status === 'Pending' ? ' selected' : '') . '>Pending</option>
        <option value="Partial"' . ($status === 'Partial' ? ' selected' : '') . '>Partial</option>
        <option value="Paid"' . ($status === 'Paid' ? ' selected' : '') . '>Paid</option>';

    $rows = '';
    foreach ($fees as $fee) {
        $rows .= '<tr>
            <td>' . e($fee['id']) . '</td>
            <td>' . e($fee['pupil_name']) . '</td>
            <td>' . e($fee['term']) . '</td>
            <td>' . formatCurrency($fee['amount_due']) . '</td>
            <td>' . formatCurrency($fee['amount_paid']) . '</td>
            <td>' . formatCurrency($fee['balance']) . '</td>
            <td><span class="badge status-' . strtolower(e($fee['payment_status'])) . '">' . e($fee['payment_status']) . '</span></td>
            <td>
                <div class="actions">
                    <button type="button" class="btn small btn-secondary" data-edit-fee="' . e($fee['id']) . '" data-pupil="' . e($fee['pupil_id']) . '" data-term="' . e($fee['term']) . '" data-amount-due="' . e($fee['amount_due']) . '" data-amount-paid="' . e($fee['amount_paid']) . '">Edit</button>
                    <form method="post" class="inline-form" onsubmit="return confirm(\'Delete this fee record?\');">
                        <input type="hidden" name="action" value="delete_fee">
                        <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                        <input type="hidden" name="id" value="' . e($fee['id']) . '">
                        <button type="submit" class="btn small btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Fee management</p>
                <h3>Fees</h3>
            </div>
            <button type="button" class="btn btn-primary" data-open-modal="feeModal">+ Record Fee</button>
        </div>

        <div class="toolbar-row">
            <form method="get" class="filter-form" action="' . appUrl('index.php?page=fees') . '">
                <input type="hidden" name="page" value="fees">
                <select name="term">
                    <option value="">All terms</option>
                    ' . $termOptions . '
                </select>
                <select name="status">
                    ' . $statusOptions . '
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="panel table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Fee ID</th>
                        <th>Pupil</th>
                        <th>Term</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Still Expected</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $rows . '
                </tbody>
            </table>
        </div>
    </section>

    <div id="feeModal" class="modal hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Add or Edit Fee Record</h4>
                <button type="button" class="close-modal" data-close-modal="feeModal">×</button>
            </div>
            <form method="post" action="' . appUrl('index.php?page=fees') . '">
                <input type="hidden" name="action" value="save_fee">
                <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                <input type="hidden" name="id" id="feeId" value="0">
                <div class="form-grid two-columns">
                    <label>
                        <span>Pupil</span>
                        <select name="pupil_id" id="feePupil" required>
                            <option value="">Select pupil</option>
                            ' . renderPupilOptions() . '
                        </select>
                    </label>
                    <label>
                        <span>Term</span>
                        <select name="term" id="feeTerm" required>
                            <option value="">Select term</option>
                            <option value="Term 1">Term 1</option>
                            <option value="Term 2">Term 2</option>
                            <option value="Term 3">Term 3</option>
                        </select>
                    </label>
                    <label>
                        <span>Amount Due</span>
                        <input type="number" name="amount_due" id="feeAmountDue" min="0.01" step="0.01" required>
                    </label>
                    <label>
                        <span>Amount Paid</span>
                        <input type="number" name="amount_paid" id="feeAmountPaid" min="0" step="0.01" value="0" required>
                    </label>
                </div>
                <p id="feeBalancePreview" class="form-hint">Amount still expected: RWF 0</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-modal="feeModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Fee</button>
                </div>
            </form>
        </div>
    </div>';
}

function renderUsersPage(array $data): string
{
    $users = $data['users'];
    $parents = $data['parents'];

    $parentOptions = '<option value="">Select parent record</option>';
    foreach ($parents as $parent) {
        $parentOptions .= '<option value="' . e($parent['id']) . '">' . e($parent['full_name']) . ' — ' . e($parent['email']) . '</option>';
    }

    $rows = '';
    foreach ($users as $userItem) {
        $rows .= '<tr>
            <td>' . e($userItem['id']) . '</td>
            <td>' . e($userItem['full_name']) . '</td>
            <td>' . e($userItem['username']) . '</td>
            <td>' . e($userItem['email']) . '</td>
            <td><span class="badge status-' . strtolower(e($userItem['role'])) . '">' . e($userItem['role']) . '</span></td>
            <td>' . e($userItem['parent_name'] ?? '—') . '</td>
            <td>
                <div class="actions">
                    <button type="button" class="btn small btn-secondary" data-edit-user="' . e($userItem['id']) . '" data-user-name="' . e($userItem['full_name']) . '" data-username="' . e($userItem['username']) . '" data-email="' . e($userItem['email']) . '" data-role="' . e($userItem['role']) . '" data-parent="' . e($userItem['parent_id'] ?? '') . '">Edit</button>
                    <form method="post" class="inline-form" onsubmit="return confirm(\'Delete this system user?\');">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                        <input type="hidden" name="id" value="' . e($userItem['id']) . '">
                        <button type="submit" class="btn small btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">User access</p>
                <h3>Users</h3>
            </div>
            <button type="button" class="btn btn-primary" data-open-modal="userModal">+ Add User</button>
        </div>

        <div class="panel table-panel">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Parent Record</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ' . $rows . '
                </tbody>
            </table>
        </div>
    </section>

    <div id="userModal" class="modal hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h4>Add or Edit User</h4>
                <button type="button" class="close-modal" data-close-modal="userModal">×</button>
            </div>
            <form method="post" action="' . appUrl('index.php?page=users') . '">
                <input type="hidden" name="action" value="save_user">
                <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
                <input type="hidden" name="id" id="userId" value="0">
                <div class="form-grid two-columns">
                    <label>
                        <span>Full name</span>
                        <input type="text" name="full_name" id="userFullName" required>
                    </label>
                    <label>
                        <span>Username</span>
                        <input type="text" name="username" id="userUsername" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="email" id="userEmail" required>
                    </label>
                    <label>
                        <span>Role</span>
                        <select name="role" id="userRole" required>
                            <option value="admin">Administrator</option>
                            <option value="staff">Staff</option>
                            <option value="parent">Parent</option>
                        </select>
                    </label>
                    <label id="linkedParentField" class="full-width hidden">
                        <span>Parent record to link</span>
                        <select name="parent_id" id="userParent">
                            ' . $parentOptions . '
                        </select>
                        <small>Each parent account links to one parent record; that parent can have multiple pupils.</small>
                    </label>
                    <label class="full-width">
                        <span>Password</span>
                        <input type="password" name="password" id="userPassword" placeholder="Leave blank to keep current password">
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-modal="userModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>';
}

function renderFamilyPage(array $data): string
{
    $parent = $data['parent'];
    $rows = '';

    foreach ($data['pupils'] as $pupil) {
        $status = $pupil['payment_status'] ?: 'No fee recorded';
        $statusClass = $pupil['payment_status'] ? 'status-' . strtolower($pupil['payment_status']) : '';
        $rows .= '<tr>
            <td>' . e($pupil['full_name']) . '</td>
            <td>' . e($pupil['class_name'] ?? '—') . '</td>
            <td>' . e(shortDate($pupil['date_of_birth'])) . '</td>
            <td>' . e($pupil['term'] ?? '—') . '</td>
            <td>' . formatCurrency($pupil['amount_due']) . '</td>
            <td>' . formatCurrency($pupil['amount_paid']) . '</td>
            <td>' . formatCurrency($pupil['balance']) . '</td>
            <td><span class="badge ' . e($statusClass) . '">' . e($status) . '</span></td>
        </tr>';
    }

    if ($rows === '') {
        $rows = '<tr><td colspan="8">No pupils are linked to your parent record yet. Please contact the school administrator.</td></tr>';
    }

    return '<section class="page-shell">
        <div class="welcome-panel">
            <div>
                <p class="eyebrow accent">Parent portal</p>
                <h3>Welcome, ' . e($parent['full_name']) . '</h3>
                <p>View your children, their class information, and any fees still expected.</p>
            </div>
        </div>
        <div class="panel table-panel">
            <div class="panel-header">
                <h4>My Children and Fee Balances</h4>
            </div>
            <table>
                <thead>
                    <tr><th>Pupil</th><th>Class</th><th>Date of Birth</th><th>Terms</th><th>Total Due</th><th>Paid</th><th>Still Expected</th><th>Status</th></tr>
                </thead>
                <tbody>' . $rows . '</tbody>
            </table>
        </div>
    </section>';
}

function renderReportsPage(array $data): string
{
    $summary = $data['summary'];
    $paymentSummary = $data['paymentSummary'];

    $statusRows = '';
    foreach (['Pending', 'Partial', 'Paid'] as $status) {
        $value = 0;
        foreach ($paymentSummary as $item) {
            if ($item['payment_status'] === $status) {
                $value = (int) $item['total'];
                break;
            }
        }
        $statusRows .= '<div class="metric-block"><span>' . e($status) . '</span><strong>' . e($value) . '</strong></div>';
    }

    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Reports</p>
                <h3>School Reports</h3>
            </div>
        </div>

        <div class="report-grid">
            <div class="report-card">
                <span>Total Registered Pupils</span>
                <strong>' . e($summary['total_pupils']) . '</strong>
            </div>
            <div class="report-card">
                <span>Total Parents</span>
                <strong>' . e($summary['total_parents']) . '</strong>
            </div>
            <div class="report-card">
                <span>Total Classes</span>
                <strong>' . e($summary['total_classes']) . '</strong>
            </div>
            <div class="report-card">
                <span>Total Fees Collected</span>
                <strong>' . formatCurrency($summary['total_fees_collected']) . '</strong>
            </div>
            <div class="report-card">
                <span>Total Outstanding Fees</span>
                <strong>' . formatCurrency($summary['total_outstanding_fees']) . '</strong>
            </div>
        </div>

        <div class="panel table-panel">
            <div class="panel-header">
                <h4>Payment Summaries</h4>
            </div>
            <div class="summary-metrics">
                ' . $statusRows . '
            </div>
        </div>
    </section>';
}

function renderSettingsPage(): string
{
    return '<section class="page-shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">System settings</p>
                <h3>Settings</h3>
            </div>
        </div>

        <div class="panel form-panel">
            <div class="panel-header">
                <h4>School Profile</h4>
            </div>
            <form class="settings-form">
                <div class="form-grid two-columns">
                    <label>
                        <span>School name</span>
                        <input type="text" value="Little Blossoms Nursery School">
                    </label>
                    <label>
                        <span>Academic year</span>
                        <input type="text" value="2026/2027">
                    </label>
                    <label>
                        <span>Contact email</span>
                        <input type="email" value="admin@littleblossoms.edu">
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" value="+250 788 123 456">
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </section>';
}

function renderLoginPage(): string
{
    return '<div class="login-card">
        <div class="login-brand">
            <div class="brand-mark">N</div>
            <div>
                <h2>Nursery School</h2>
                <p>Management System</p>
            </div>
        </div>

        <form method="post" action="' . appUrl('index.php?page=login') . '" class="login-form">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">
            <h3>Welcome back</h3>
            <p class="login-subtitle">Sign in to continue</p>

            <label>
                <span>Username or Email</span>
                <input type="text" name="username" placeholder="Enter username or email" required>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" placeholder="Enter password" required>
            </label>

            <button type="submit" class="btn btn-primary login-button">Login</button>
        </form>
    </div>';
}

function renderPupilOptions(): string
{
    $db = Database::getConnection();
    $stmt = $db->query('SELECT id, full_name FROM pupils ORDER BY full_name ASC');
    $pupils = $stmt->fetchAll();

    $html = '';
    foreach ($pupils as $pupil) {
        $html .= '<option value="' . e($pupil['id']) . '">' . e($pupil['full_name']) . '</option>';
    }

    return $html;
}

switch ($page) {
    case 'login':
        $title = 'Login';
        $content = renderLoginPage();
        break;
    case 'dashboard':
        $title = 'Dashboard';
        $content = renderDashboardPage(DashboardController::index($pdo), $user ?? ['full_name' => 'Administrator', 'role' => 'admin']);
        break;
    case 'pupils':
        $title = 'Pupils';
        $content = renderPupilsPage(PupilController::index($pdo));
        break;
    case 'parents':
        $title = 'Parents';
        $content = renderParentsPage(ParentController::index($pdo));
        break;
    case 'classes':
        $title = 'Classes';
        $content = renderClassesPage(ClassController::index($pdo));
        break;
    case 'fees':
        $title = 'Fees';
        $content = renderFeesPage(FeeController::index($pdo));
        break;
    case 'users':
        $title = 'Users';
        $content = renderUsersPage(UserController::index($pdo));
        break;
    case 'family':
        $title = 'My Children';
        $content = renderFamilyPage(ParentController::portal($pdo));
        break;
    case 'reports':
        $title = 'Reports';
        $content = renderReportsPage(DashboardController::reports($pdo));
        break;
    case 'settings':
        $title = 'Settings';
        $content = renderSettingsPage();
        break;
    default:
        header('Location: ' . appUrl('index.php?page=dashboard'));
        exit;
}

include __DIR__ . '/../app/Views/layout.php';
