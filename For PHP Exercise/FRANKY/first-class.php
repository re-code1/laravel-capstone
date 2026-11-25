<?php

class Grade
{
    public string $subject;
    public int $semester;
    public ?float $value;

    public function isMissing(): bool
    {
        return $this->value === null;
    }

    public function isPassing(): bool
    {
        return !$this->isMissing() && $this->value >= 75;
    }
}

$science = new Grade();
$science->subject = 'Science 10';
$science->semester = 2;
$science->value = null;

var_dump($science->isMissing());   // bool(true)

$math = new Grade();
$math->subject = 'Mathematics 10';
$math->semester = 1;
$math->value = 85.5;

var_dump($math->isMissing());      // bool(false)