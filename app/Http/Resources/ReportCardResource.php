<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// The JSON plate for one report card. Built from ['student' => Student, 'card' => the service's array].
// Only the fields listed here ever leave the server.
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
            'subjects' => $card['rows']->values()->map(fn (array $row) => [
                'subject' => $row['subject']->name,
                'sem1' => $row['sem1'],
                'sem2' => $row['sem2'],
                'final' => $row['final'],
            ]),
            'general_average' => $card['average'],
            'remark' => $card['remark'],
        ];
    }
}
