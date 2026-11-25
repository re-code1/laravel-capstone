<?php

class ReportCard
{
    public const PASSING = 75;

    public function __construct(private Student $student) {}

    /**
     * Average of the subject averages, unrounded.
     * Missing grades are skipped; returns null when there is nothing to average.
     */
    private function rawAverage(): ?float
    {
        $bySubject = [];
        foreach ($this->student->grades() as $grade) {
            if (! $grade->isMissing()) {
                $bySubject[$grade->subject][] = $grade->value;
            }
        }
        if (count($bySubject) === 0) {
            return null;
        }

        $subjectAverages = [];
        foreach ($bySubject as $values) {
            $subjectAverages[] = array_sum($values) / count($values);
        }

        return array_sum($subjectAverages) / count($subjectAverages);
    }

    // Rounded for display only. Do not use this to decide pass or fail.
    public function generalAverage(): ?float
    {
        $average = $this->rawAverage();

        return $average === null ? null : round($average, 2);
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

        // The average decides: a student can pass overall even if one subject is below PASSING.
        // Compare the unrounded average so 74.996 does not become 75.
        return $this->rawAverage() >= self::PASSING ? 'PASSED' : 'FAILED';
    }
}
