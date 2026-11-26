<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportCardResource;
use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\Support\Facades\Gate;

class ReportCardController extends Controller
{
    // Same card the web page shows: the service cooks it, the resource plates it as JSON.
    public function show(Student $student, ReportCardService $cards): ReportCardResource
    {
        $student->load('section');
        Gate::authorize('view', $student->section);

        return new ReportCardResource([
            'student' => $student,
            'card' => $cards->card($student),
        ]);
    }
}
