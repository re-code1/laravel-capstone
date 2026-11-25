<?php

// Five kinds of value
$name = 'Juan Dela Cruz';   // string: text
$quarter = 2;               // int: whole number
$grade = 88.25;             // float: number with decimals
$enrolled = true;           // bool: true or false
$science = null;            // null: no value yet

// Joining text: the dot operator, or variables inside double quotes
echo 'Student: ' . $name . "\n";
echo "Quarter $quarter grade: $grade\n";

// Decisions
if ($grade >= 90) {
    echo "With honours\n";
} elseif ($grade >= 95) {
    echo "Passed\n";
} else {
    echo "Failed\n";
}

// ?? means "use this if the left side is null"
echo 'Science: ' . ($science ?? 'INC') . "\n";

// See the type and value of anything
var_dump($grade, $science);
var_dump(0 == null, 0 === null);