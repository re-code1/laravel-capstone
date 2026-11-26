<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Collection;

// The kitchen: every grading rule of the school lives here, once.
// Controllers and views never calculate; they ask this class.
class ReportCardService
{
    // 75 and above is PASSED, below 75 is FAILED.
    public const PASSING = 75;

    // The label for a missing grade. Written here once; the views just print it.
    public const INCOMPLETE = 'INC';

    // Everything one report card page needs.
    // loadMissing() fetches the section, its adviser, the grades and each
    // grade's subject in a few queries, and skips what is already loaded.
    public function card(Student $student): array
    {
        $student->loadMissing(['section.adviser', 'grades.subject']);

        return [
            'rows' => $this->rows($student),
            'average' => $this->generalAverage($student),
            'remark' => $this->remark($student),
        ];
    }

    // The class list of one section: each student with their average and remark.
    // with('grades') loads every student's grades in ONE query (no N+1).
    public function classList(Section $section): Collection
    {
        $students = $section->students()->with('grades')->orderBy('last_name')->get();

        return $students->map(fn (Student $student) => [
            'student' => $student,
            'average' => $this->generalAverage($student),
            'remark' => $this->remark($student),
        ]);
    }

    // Plain average of every grade that is not NULL, rounded to 2 decimals.
    // Returns null when the student has no grades at all.
    public function generalAverage(Student $student): ?float
    {
        $average = $student->grades->whereNotNull('grade')->avg('grade');

        return $average === null ? null : round($average, 2);
    }

    // The order matters: missing grades are checked before the average,
    // because an incomplete record must never be judged PASSED or FAILED.
    public function remark(Student $student): string
    {
        if ($student->grades->isEmpty()) {
            return 'NO GRADES';
        }

        if ($student->grades->whereNull('grade')->isNotEmpty()) {
            return self::INCOMPLETE;
        }

        return $this->generalAverage($student) >= self::PASSING ? 'PASSED' : 'FAILED';
    }

    // One row per subject, with semester 1 and semester 2 side by side.
    // final is the average of both semesters, or null if either is missing.
    // A missing semester is returned as INC, ready for the view to print.
    public function rows(Student $student): Collection
    {
        return $student->grades->groupBy('subject_id')->map(function (Collection $grades) {
            $sem1 = $grades->firstWhere('semester', 1)?->grade;
            $sem2 = $grades->firstWhere('semester', 2)?->grade;

            $final = ($sem1 !== null && $sem2 !== null) ? round(($sem1 + $sem2) / 2, 2) : null;

            return [
                'subject' => $grades->first()->subject,
                'sem1' => $sem1 ?? self::INCOMPLETE,
                'sem2' => $sem2 ?? self::INCOMPLETE,
                'final' => $final,
                'remark' => $this->subjectRemark($final),
            ];
        });
    }

    // PASSED or FAILED for one subject, using the same passing grade and the
    // same rounded value as the general average. A missing final is INC.
    private function subjectRemark(?float $final): string
    {
        if ($final === null) {
            return self::INCOMPLETE;
        }

        return $final >= self::PASSING ? 'PASSED' : 'FAILED';
    }
}
