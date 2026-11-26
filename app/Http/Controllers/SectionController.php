<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\ReportCardService;
use Illuminate\View\View;

// The waiter for the section pages: takes the order, asks the pantry or the
// kitchen, and carries the result to the view. It never calculates anything.
class SectionController extends Controller
{
    // All sections, with each adviser and the number of students.
    // with() and withCount() fetch these in 2 queries, not one per section.
    public function index(): View
    {
        $sections = Section::with('adviser')->withCount('students')->orderBy('name')->get();

        return view('sections.index', ['sections' => $sections]);
    }

    // One section's class list. Laravel loads $section from the {section} in
    // the URL; the service is handed in by Laravel, so we never write "new".
    public function show(Section $section, ReportCardService $cards): View
    {
        return view('sections.show', [
            'section' => $section,
            'summaries' => $cards->classList($section),
        ]);
    }
}
