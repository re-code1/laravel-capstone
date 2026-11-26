<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

// Order of checks: token (401), authorize() (403), rules() (422), then the controller.
class UpdateGradeRequest extends FormRequest
{
    // Asks GradePolicy::update(). Returning false makes Laravel answer 403.
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('grade'));
    }

    // Grades are 60 to 100 (the Day 4 page's rule) with at most 2 decimals. null is allowed: it clears the grade (INC).
    // 'present' means the key must be sent, so an empty body is an error and not a silent no-op.
    public function rules(): array
    {
        return [
            'grade' => ['present', 'nullable', 'numeric', 'between:60,100', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade.present' => 'Send a grade, or null to clear it.',
            'grade.numeric' => 'The grade must be a number.',
            'grade.between' => 'The grade must be between 60 and 100.',
            'grade.decimal' => 'The grade can have at most 2 decimal places.',
        ];
    }
}
