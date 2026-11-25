<?php

class PupilController
{
    public static function index(PDO $db): array
    {
        $classModel = new ClassModel($db);
        $parentModel = new ParentModel($db);
        $pupilModel = new PupilModel($db);

        $search = trim($_GET['search'] ?? '');
        $classId = (int) ($_GET['class_id'] ?? 0);

        $pupils = $pupilModel->all($search, $classId);
        $classes = $classModel->all();
        $parents = $parentModel->all();

        return [
            'pupils' => $pupils,
            'classes' => $classes,
            'parents' => $parents,
            'search' => $search,
            'classId' => $classId,
        ];
    }

    public static function save(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('pupils', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $dob = trim($_POST['date_of_birth'] ?? '');
        $classId = (int) ($_POST['class_id'] ?? 0);
        $parentId = (int) ($_POST['parent_id'] ?? 0);

        if ($fullName === '' || $dob === '' || $classId <= 0 || $parentId <= 0) {
            redirect('pupils', 'Please complete all required pupil fields.', 'error');
        }

        $pupilModel = new PupilModel($db);

        if ($id > 0) {
            $pupilModel->update($id, [
                'full_name' => $fullName,
                'date_of_birth' => $dob,
                'class_id' => $classId,
                'parent_id' => $parentId,
            ]);
            redirect('pupils', 'Pupil record updated successfully.', 'success');
        }

        $pupilModel->create([
            'full_name' => $fullName,
            'date_of_birth' => $dob,
            'class_id' => $classId,
            'parent_id' => $parentId,
        ]);

        redirect('pupils', 'Pupil added successfully.', 'success');
    }

    public static function delete(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('pupils', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('pupils', 'Invalid pupil selected.', 'error');
        }

        $pupilModel = new PupilModel($db);
        $pupilModel->delete($id);
        redirect('pupils', 'Pupil deleted successfully.', 'success');
    }
}
