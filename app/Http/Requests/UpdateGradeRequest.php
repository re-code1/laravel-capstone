<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Checks who may change the grade (GradePolicy) and what value is allowed.
class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('grade'));
    }

    public function rules(): array
    {
        return [
            // present: the field must be sent. null means INC.
            'grade' => ['present', 'nullable', 'numeric', 'between:60,100', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.present' => 'Send the grade, or send it empty to mark it INC.',
            'grade.numeric' => 'The grade must be a number, or empty for INC.',
            'grade.between' => 'The grade must be between 60 and 100.',
            'grade.decimal' => 'Use at most 2 decimal places, for example 88.75.',
        ];
    }
}
