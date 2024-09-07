<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ["name"];

    public function groupPreferences(): HasMany
    {
        return $this->hasMany(GroupPreferences::class);
    }

}
