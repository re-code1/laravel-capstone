<?php

// A variable starts with $. PHP works out the type.
$name = 'Andrea Bautista';

// An array of grades. null means "no grade yet".
$grades = [0, 85.50, 89.75, 87.50, null];

// A function with types: takes an array, returns a float or null.
function average(array $values): ?float
{
    $total = 0;
    $count = 0;
    foreach ($values as $value) {
        if ($value === null) {
            continue;           // skip missing grades
        }
        $total += $value;
        $count++;
    }
    return $count === 0 ? null : round($total / $count, 2);
}

// An associative array: keys instead of numbers.
$student = ['name' => $name, 'average' => average($grades)];

// Double quotes let you put variables inside the text.
echo "{$student['name']}: {$student['average']}\n";
echo 'Missing grade? ' . (in_array(null, $grades, true) ? 'yes' : 'no') . "\n";