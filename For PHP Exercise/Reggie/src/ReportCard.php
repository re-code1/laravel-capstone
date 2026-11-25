<?php

class ReportCard
{
    public const PASSING = 75;

    public function __construct(private Student $student) {}

    public function generalAverage(): ?float
    {
        $values = [];
        foreach ($this->student->grades() as $grade) {
            if (! $grade->isMissing()) {
                $values[] = $grade->value;
            }
        }
        if (count($values) === 0) {
            return null;
        }

        return round(array_sum($values) / count($values), 2);
    }

    public function remark(): string
    {
        $grades = $this->student->grades();
        if (count($grades) === 0) {
            return 'NO GRADES';
        }
        foreach ($grades as $grade) {
            if ($grade->isMissing()) {
                return 'INC';
            }
        }

        return $this->generalAverage() >= self::PASSING ? 'PASSED' : 'FAILED';
    }
}