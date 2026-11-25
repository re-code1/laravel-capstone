<?php

namespace School;

class Student
{
    /** @var Grade[] */
    private array $grades = [];

    public function __construct(
        public string $lrn,
        public string $firstName,
        public string $lastName,
    ) {}

    public function fullName(): string
    {
        return "{$this->lastName}, {$this->firstName}";
    }

    public function addGrade(Grade $grade): void
    {
        $this->grades[] = $grade;
    }

    public function grades(): array
    {
        return $this->grades;
    }
}