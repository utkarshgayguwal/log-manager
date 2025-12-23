<?php

namespace UtkarshGayguwal\LogManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    const GENERAL = 'general';

    /**
     * Scope to filter only active records.
     */
    public function scopeActive($query, $status = true)
    {
        return $query->where('status', $status);
    }
}