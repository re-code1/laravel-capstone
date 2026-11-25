<?php

require 'src/Grade.php';
require 'src/Student.php';
require 'src/ReportCard.php';

$andrea = new Student('136512250004', 'Andrea', 'Bautista');
$andrea->addGrade(new Grade('Mathematics 10', 1, 85.50));
$andrea->addGrade(new Grade('Mathematics 10', 2, 89.75));
$andrea->addGrade(new Grade('Science 10', 1, 87.50));
$andrea->addGrade(new Grade('Science 10', 2, null));

$card = new ReportCard($andrea);

echo $andrea->fullName() . PHP_EOL;
echo 'General average: ' . $card->generalAverage() . PHP_EOL;
echo 'Remark: ' . $card->remark() . PHP_EOL;

$rafael = new Student('136512250011', 'Rafael', 'Castillo');
$rafaelCard = new ReportCard($rafael);

echo PHP_EOL;
echo $rafael->fullName() . PHP_EOL;
echo 'General average: ' . $rafaelCard->generalAverage() . PHP_EOL;
echo 'Remark: ' . $rafaelCard->remark() . PHP_EOL;
