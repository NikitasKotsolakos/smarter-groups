<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = ["name", "minimumParticipants", "maximumParticipants", "priorityGroup"];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

}
