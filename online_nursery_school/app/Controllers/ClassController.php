<?php

class ClassController
{
    public static function index(PDO $db): array
    {
        $classModel = new ClassModel($db);
        return ['classes' => $classModel->all()];
    }

    public static function save(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('classes', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $className = trim($_POST['class_name'] ?? '');
        $teacherName = trim($_POST['teacher_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($className === '' || $teacherName === '') {
            redirect('classes', 'Class name and teacher name are required.', 'error');
        }

        $classModel = new ClassModel($db);

        if ($id > 0) {
            $classModel->update($id, [
                'class_name' => $className,
                'teacher_name' => $teacherName,
                'description' => $description,
            ]);
            redirect('classes', 'Class updated successfully.', 'success');
        }

        $classModel->create([
            'class_name' => $className,
            'teacher_name' => $teacherName,
            'description' => $description,
        ]);

        redirect('classes', 'Class added successfully.', 'success');
    }

    public static function delete(PDO $db): void
    {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            redirect('classes', 'Security validation failed. Please try again.', 'error');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('classes', 'Invalid class selected.', 'error');
        }

        $classModel = new ClassModel($db);
        $classModel->delete($id);
        redirect('classes', 'Class deleted successfully.', 'success');
    }
}
