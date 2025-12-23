<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the user model class that will be used for the relationship
    | between logs and users. This should match your application's auth configuration.
    |
    */
    'user_model' => config('auth.providers.users.model', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API endpoints for the log management package.
    |
    */
    'api' => [
        'prefix' => 'api/logs',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for the log listing API.
    |
    */
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the template system for log descriptions and redirects.
    |
    */
    'templates' => [
        'default_log_template' => 'general',
        'enable_redirect_system' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Constants
    |--------------------------------------------------------------------------
    |
    | Define your module constants here. This replaces the hardcoded module
    | system from the original implementation.
    |
    | Example:
    | 'user_management' => 1,
    | 'role_management' => 2,
    | 'content_management' => 3,
    |
    | Usage: LogManagement::log($model, 'created', config('log-management.modules.user_management'), 'User Management');
    |
    */
    'modules' => [
        // Add Model constants here
        // Example: 'user_management' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Configuration
    |--------------------------------------------------------------------------
    |
    | Define additional action constants beyond the basic CRUD actions.
    | The package already includes: created, updated, deleted
    |
    | Example:
    | 'approved' => 'approved',
    | 'rejected' => 'rejected',
    | 'exported' => 'exported',
    |
    */
    'actions' => [
        // Add more action constants as needed
        // Example: 'approved' => 'approved',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database connection and table names if you want to override defaults.
    |
    */
    'database' => [
        'connection' => null, // Use default connection
        'tables' => [
            'logs' => 'logs',
            'log_templates' => 'log_templates',
            'redirect_templates' => 'redirect_templates',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-discovery Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which components should be automatically loaded by the package.
    |
    */
    'auto_discover' => [
        'routes' => true,
        'migrations' => true,
        'seeders' => true,
    ],
];