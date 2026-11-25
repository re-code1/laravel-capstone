<?php

require __DIR__ . '/../vendor/autoload.php';

use School\Grade;
use School\ReportCard;
use School\Student;

$andrea = new Student('136512250004', 'Andrea', 'Bautista');
$andrea->addGrade(new Grade('Mathematics 10', 1, 85.50));
$andrea->addGrade(new Grade('Mathematics 10', 2, 89.75));
$andrea->addGrade(new Grade('Science 10', 1, 87.50));
$andrea->addGrade(new Grade('Science 10', 2, null));

$rafael = new Student('136512250011', 'Rafael', 'Castillo');

$card = new ReportCard($rafael);

echo $rafael->fullName() . PHP_EOL;
echo 'General average: ' . $card->generalAverage() . PHP_EOL;
echo 'Remark: ' . $card->remark() . PHP_EOL;