<?php

namespace UtkarshGayguwal\LogManagement\Services;

use UtkarshGayguwal\LogManagement\Models\Log;
use UtkarshGayguwal\LogManagement\Models\LogTemplate;
use UtkarshGayguwal\LogManagement\Models\RedirectTemplate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class LoggerService
{
    protected array $redirection;

    public function __construct() {}

    /**
     * Log a single entity action
     */
    public function log($loggable, string $action, string $moduleId, string $moduleName, array $options = []): void
    {
        try {
            if (is_null($loggable)) {
                $loggable = Auth::user();
            }

            if (in_array($action, [Log::ACTION_DELETED], true)) {
                Log::where('loggable_id', $loggable->id)
                ->where('module_id', $moduleId)
                ->update(['is_redirect_enabled' => 0]);
            }

            $loggableType = array_flip(Relation::morphMap())[get_class($loggable)] ?? get_class($loggable);
            $options['module_name'] = $moduleName;
            $description = $this->buildDescription($action, $moduleName, $options);
            $redirectPath = $this->generateRedirectPath($options);

            Log::create([
                'loggable_id' => $loggable->id,
                'loggable_type' => $loggableType,
                'module_id' => $moduleId,
                'client_id' => $options['client_id'] ?? null,
                'program_id' => $options['program_id'] ?? null,
                'asset_id' => $options['asset_id'] ?? null,
                'log_type' => $options['log_type'] ?? Log::SOURCE_USER,
                'action' => $action,
                'description' => $description,
                'redirect_path' => $redirectPath,
                'ip_address' => $options['ip_address'] ?? Request::ip(),
                'data' => $options,
                'created_by' => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            LaravelLog::error('Logging failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Log multiple entities in bulk (IDs only)
     */
    public function logBulk(string $modelClass, array $ids, string $action, string $moduleId, string $moduleName, array $options = []): void
    {
        try {
            $loggableType = array_flip(Relation::morphMap())[$modelClass] ?? $modelClass;
            $options['module_name'] = $moduleName;

            $fields = $this->getExistingFields(
                $modelClass,
                $this->redirection['url_fields'] ?? [],
                $loggableType
            );

            $instances = $modelClass::whereIn('id', $ids)
            ->withTrashed()
            ->get($fields);

            if ($instances->isEmpty()) {
                return;
            }

            $descriptionTemplate = $options['has_template'] ?? true
                ? $this->getTemplateBody($options['template_type'] ?? LogTemplate::GENERAL)
                : null;

            $now = now();
            $userId = Auth::id();
            $ip = $options['ip_address'] ?? Request::ip();

            $logs = $instances->map(function ($instance) use ($moduleId, $action, $options, $moduleName, $descriptionTemplate, $now, $userId, $ip, $loggableType) {
                $description = $descriptionTemplate
                ? $this->interpolateTemplate($action, $moduleName, $descriptionTemplate, $options)
                : ($options['description'] ?? '');

                $instanceArray = $instance->toArray();
                $redirectPath = isset($this->redirection['redirect_constant'])
                    ? $this->generateRedirectPath(array_merge(['redirect_constant' => $this->redirection['redirect_constant']], $instanceArray))
                    : null;

                if (in_array($action, [Log::ACTION_DELETED], true)) {
                    Log::where('loggable_id', $instance->id)
                    ->where('module_id', $moduleId)
                    ->update(['is_redirect_enabled' => 0]);
                }

                return [
                    'loggable_id' => $instance->id,
                    'loggable_type' => $loggableType,
                    'module_id' => $moduleId,
                    'client_id' => $instance->client_id ?? null,
                    'program_id' => $instance->program_id ?? null,
                    'asset_id' => $instance->asset_id ?? null,
                    'log_type' => $options['log_type'] ?? Log::SOURCE_USER,
                    'redirect_path' => $redirectPath,
                    'action' => $action,
                    'description' => $description,
                    'ip_address' => $ip,
                    'data' => json_encode($options),
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

            Log::insert($logs->toArray());
        } catch (\Throwable $e) {
            LaravelLog::error('Bulk logging failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Set redirection configuration for bulk logging
     */
    public function setRedirection(string $redirectConstant, array $urlFields = [])
    {
        $this->redirection = [
            'redirect_constant' => $redirectConstant,
            'url_fields' => $urlFields,
        ];

        return $this;
    }

    /**
     * Generate redirect path using complex interpolation
     */
    protected function generateRedirectPath(array $variables = []): ?string
    {
        try {
            if (!isset($variables['redirect_constant'])) {
                return null;
            }

            $template = RedirectTemplate::where('name', $variables['redirect_constant'])->value('path');

            if (! $template) {
                return null;
            }

            foreach ($variables as $key => $value) {
                $template = str_replace('{'.$key.'}', $value, $template);
            }

            return $template;
        } catch (\Throwable $th) {
            report($th);

            return null;
        }
    }

    /**
     * Generate simplified redirect path using basic string replacement
     */
    protected function generateSimpleRedirectPath(array $variables = []): ?string
    {
        try {
            if (!isset($variables['redirect_constant'])) {
                return null;
            }

            $template = RedirectTemplate::where('name', $variables['redirect_constant'])->value('path');

            if (! $template) {
                return null;
            }

            // Simple string replacement without complex logic
            foreach ($variables as $key => $value) {
                if (is_string($value)) {
                    $template = str_replace('{' . $key . '}', $value, $template);
                }
            }

            return $template;
        } catch (\Throwable $th) {
            report($th);

            return null;
        }
    }

    private function buildDescription(string $action, string $moduleName, array $options): string
    {
        if (($options['has_template'] ?? true) === true) {
            $templateBody = $this->getTemplateBody($options['template_type'] ?? LogTemplate::GENERAL);

            return $this->interpolateTemplate($action, $moduleName, $templateBody, $options);
        }

        if (empty($options['description'])) {
            throw new \InvalidArgumentException('Description is required when template is disabled');
        }

        return $options['description'];
    }

    private function getTemplateBody(string $templateType): string
    {
        $template = LogTemplate::where('name', $templateType)->first();
        if (! $template) {
            throw new \RuntimeException("Log template '{$templateType}' not found");
        }

        return $template->body;
    }

    private function interpolateTemplate(string $action, string $moduleName, string $template, array $variables): string
    {
        $now = now();
        // Default template variables
        $defaults = [
            'action' => $action,
            'module_name' => $moduleName,
            'date' => $now->format('d F Y'),
            'time' => $now->format('h:i A'),
            'model_name' => $variables['module_name'],
            'staff_name' => $variables['staff_name'] ?? Auth::user()->name ?? 'System',
        ];

        $variables = array_merge($defaults, $variables);

        foreach ($variables as $key => $value) {
            $template = str_replace('{'.$key.'}', $value, $template);
        }

        return $template;
    }

    protected function getExistingFields(string $modelClass, array $urlFields = [], $loggableType = null): array
    {
        $fields = array_merge(
            ['id'],
            $urlFields
        );

        return array_values(array_filter($fields, function ($field) use ($modelClass) {
            return Schema::hasColumn((new $modelClass)->getTable(), $field);
        }));
    }
}