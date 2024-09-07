<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupPreferences extends Model
{
    public function group(): HasOne
    {
        return $this->hasOne(Group::class);
    }
}
