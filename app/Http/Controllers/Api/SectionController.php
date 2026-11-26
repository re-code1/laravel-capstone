<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassListEntryResource;
use App\Models\Section;
use App\Services\ReportCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    // The sections this user may see, with a student count (one query, no N+1).
    public function index(Request $request): JsonResponse
    {
        $sections = Section::visibleTo($request->user())
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $sections->map(fn (Section $section) => [
                'id' => $section->id,
                'name' => $section->name,
                'grade_level' => $section->grade_level,
                'students' => $section->students_count,
            ]),
        ]);
    }

    // The class list of one section, after the policy says this user may see it.
    public function students(Section $section, ReportCardService $cards): AnonymousResourceCollection
    {
        Gate::authorize('view', $section);

        return ClassListEntryResource::collection($cards->classList($section));
    }
}
