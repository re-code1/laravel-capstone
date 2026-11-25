<?php

require __DIR__ . '/Grade.php';
require __DIR__ . '/Student.php';

$andrea = new Student('123456789012', 'Andrea', 'Cruz');
$andrea->addGrade(new Grade('Math', 1, 90.0));
$andrea->addGrade(new Grade('Science', 1, 85.5));

echo $andrea->fullName();
