<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\View\View;

// The waiter for one student's report card. The kitchen (the service) cooks
// the card; this class only passes the order in and the plate out.
class ReportCardController extends Controller
{
    public function show(Student $student, ReportCardService $cards): View
    {
        $card = $cards->card($student);

        return view('students.report-card', [
            'student' => $student,
            'rows' => $card['rows'],
            'average' => $card['average'],
            'remark' => $card['remark'],
        ]);
    }
}
