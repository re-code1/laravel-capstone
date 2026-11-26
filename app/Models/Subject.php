<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// No column may be changed through a request.
#[Fillable([])]
class Subject extends Model
{
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
