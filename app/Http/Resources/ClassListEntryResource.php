<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// One line of a class list. Built from one item of ReportCardService::classList():
// ['student' => Student, 'average' => ?float, 'remark' => string].
class ClassListEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->resource['student'];

        return [
            'id' => $student->id,
            'lrn' => $student->lrn,
            'name' => $student->fullName(),
            'general_average' => $this->resource['average'],
            'remark' => $this->resource['remark'],
        ];
    }
}
