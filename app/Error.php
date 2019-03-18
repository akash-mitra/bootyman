<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    protected $fillable = ['type', 'provider', 'region', 'errorable_type', 'errorable_id', 'desc', 'status', 'token'];

    /**
     * Get all of the owning errorable models.
     */
    public function errorable()
    {
        return $this->morphTo();
    }
}
