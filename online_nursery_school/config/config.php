<?php

define('APP_NAME', 'Online Nursery School Management System');
define('DB_HOST', 'localhost');
define('DB_PORT', (int) (getenv('NURSERY_DB_PORT') ?: 3306));
define('DB_NAME', 'nursery_school');
define('DB_USER', getenv('NURSERY_DB_USER') ?: 'root');
define('DB_PASS', getenv('NURSERY_DB_PASS') ?: '');
define('APP_URL', 'http://localhost/student_management_system/online_nursery_school/public');

function appUrl($path = '')
{
    $base = rtrim(APP_URL, '/');
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}
