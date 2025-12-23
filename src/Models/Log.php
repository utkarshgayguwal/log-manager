<?php

namespace UtkarshGayguwal\LogManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Log extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Basic CRUD actions - Add more action constants as needed
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';

    const SOURCE_USER = 'user';
    const SOURCE_SYSTEM = 'system';

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the parent loggable model (polymorphic relationship).
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the log.
     * Note: This assumes a User model exists. Adjust if your app uses different user model.
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'), 'created_by');
    }

    /**
     * Scope to filter logs by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter logs by module ID.
     * Note: Apps should manage their own module constants.
     * Example: Define constants like const USER_MANAGEMENT = 1; in your app.
     */
    public function scopeByModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope to filter logs by log type (user/system).
     */
    public function scopeByType($query, $type)
    {
        return $query->where('log_type', $type);
    }

    /**
     * Scope to get logs where redirect is enabled.
     */
    public function scopeWithRedirect($query, $enabled = true)
    {
        return $query->where('is_redirect_enabled', $enabled);
    }

    /**
     * Scope to search logs by description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('description', 'LIKE', "%{$term}%");
    }
}