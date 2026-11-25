<?php
$student = $_GET['name'] ?? 'Juan Dela Cruz';
$grades = [
    'Mathematics 10' => 88.25,
    'Science 10' => 89.00,
    'English 10' => 95.75,
];
$passingGrade = 85;
$average = round(array_sum($grades) / count($grades), 2);
?>
<!doctype html>
<html>
<head><title>Report card</title></head>
<body>
    <h1><?= htmlspecialchars($student) ?></h1>
    <table border="1">
        <tr><th>Subject</th><th>Grade</th><th>Remarks</th></tr>
        <?php foreach ($grades as $subject => $grade): ?>
            <?php $passed = $grade >= $passingGrade; ?>
            <tr>
                <td><?= $subject ?></td>
                <td><?= $grade ?></td>
                <td style="color: <?= $passed ? 'green' : 'red' ?>;"><?= $passed ? 'PASSED' : 'FAILED' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>General average: <b><?= $average ?></b></p>
</body>
</html>