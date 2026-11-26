<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Shapes one report card as JSON. It only picks and renames; every rule
// (averages, INC, remarks) was already decided by ReportCardService.
// Built from ['student' => Student, 'card' => the array from the service].
class ReportCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->resource['student'];
        $card = $this->resource['card'];

        return [
            'lrn' => $student->lrn,
            'name' => $student->fullName(),
            'section' => $student->section->name,
            'subjects' => $card['rows']->map(fn (array $row) => [
                'subject' => $row['subject']->name,
                'sem1' => $this->asNumber($row['sem1']),
                'sem2' => $this->asNumber($row['sem2']),
                'final' => $row['final'],
            ])->values(),
            'general_average' => $card['average'],
            'remark' => $card['remark'],
        ];
    }

    // MariaDB hands DECIMAL back as a string ("85.50"). Show it as a JSON number,
    // but leave text such as "INC" alone.
    private function asNumber(mixed $value): mixed
    {
        return is_numeric($value) ? (float) $value : $value;
    }
}
