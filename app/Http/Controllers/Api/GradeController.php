<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    // The request class has already checked permission and the value.
    public function update(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $grade->update($request->validated());

        return response()->json([
            'id' => $grade->id,
            'student_id' => $grade->student_id,
            'subject_id' => $grade->subject_id,
            'semester' => $grade->semester,
            // DECIMAL comes back as a string ("90.00"); send a number, or null for INC.
            'grade' => $grade->grade === null ? null : (float) $grade->grade,
        ]);
    }
}
