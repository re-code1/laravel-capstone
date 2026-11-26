<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;

class GradeController extends Controller
{
    // By the time this runs, the token, the policy and the rules have all said yes.
    // Only validated() is saved, never the whole request.
    public function update(UpdateGradeRequest $request, Grade $grade): GradeResource
    {
        $grade->update($request->validated());

        return new GradeResource($grade);
    }
}
