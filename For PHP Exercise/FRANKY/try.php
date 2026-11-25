<?php
require 'src/Grade.php';
require 'src/Student.php';

$andrea = new Student('123456789012', 'Andrea', 'Bautista');
$andrea->addGrade(new Grade('Mathematics 10', 1, 85.5));
$andrea->addGrade(new Grade('Science 10', 1, 89.0));

echo $andrea->fullName();
