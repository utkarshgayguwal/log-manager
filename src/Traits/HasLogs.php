<?php

namespace UtkarshGayguwal\LogManagement\Traits;

use UtkarshGayguwal\LogManagement\Models\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLogs
{
    /**
     * Get all logs for this model.
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    /**
     * Get logs by action type.
     */
    public function getLogsByAction(string $action): MorphMany
    {
        return $this->logs()->byAction($action);
    }

    /**
     * Get logs by module ID.
     */
    public function getLogsByModule(int $moduleId): MorphMany
    {
        return $this->logs()->byModule($moduleId);
    }

    /**
     * Get logs by type (user/system).
     */
    public function getLogsByType(string $type): MorphMany
    {
        return $this->logs()->byType($type);
    }

    /**
     * Get only logs that have redirect enabled.
     */
    public function getLogsWithRedirect(bool $enabled = true): MorphMany
    {
        return $this->logs()->withRedirect($enabled);
    }
}