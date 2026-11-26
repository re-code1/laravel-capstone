<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // Only the sections the signed-in user may see, with the student count.
    public function index(Request $request): JsonResponse
    {
        $sections = Section::visibleTo($request->user())
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $sections->map(fn (Section $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'grade_level' => $s->grade_level,
                'students_count' => $s->students_count,
            ])->values(),
        ]);
    }
}
