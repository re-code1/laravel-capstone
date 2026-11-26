<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportCardResource;
use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\Support\Facades\Gate;

// The API waiter for one report card. Same kitchen (ReportCardService) as the
// web page; only the plate differs: JSON here, a Blade view there.
class ReportCardController extends Controller
{
    public function show(Student $student, ReportCardService $cards): ReportCardResource
    {
        // 403 unless the user is the admin or advises this student's section.
        Gate::authorize('view', $student->section);

        return new ReportCardResource([
            'student' => $student,
            'card' => $cards->card($student),
        ]);
    }
}
