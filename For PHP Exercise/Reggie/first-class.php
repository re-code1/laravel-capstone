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
}

$science = new Grade();
$science->subject = 'Science 10';
$science->semester = 2;
$science->value = null;

var_dump($science->isMissing());   // bool(true)