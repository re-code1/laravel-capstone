<?php

class Grade
{
    public function __construct(
        public string $subject,
        public int $semester,
        public ?float $value,
    ) {}

    public function isMissing(): bool
    {
        return $this->value === null;
    }
}