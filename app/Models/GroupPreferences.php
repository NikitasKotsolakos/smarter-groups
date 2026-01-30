<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupPreferences extends Model
{

    protected $fillable = ["student_id", "group_id", "rank"];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
