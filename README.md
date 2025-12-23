# Laravel Log Management Package

A reusable Laravel package for comprehensive log management with template support and bulk logging capabilities.

## Features

- 📝 **Single & Bulk Logging**: Log individual actions or multiple records at once
- 🎯 **Template System**: Database-driven log and redirect templates
- 🔍 **Advanced Filtering**: Complex date filtering and search capabilities
- 🔄 **Redirect Integration**: Generate redirect paths from templates
- 🚀 **API Ready**: Built-in API endpoints for log management
- 🔧 **Highly Configurable**: Easy to customize and extend
- 🏗️ **Laravel Compatible**: Works with Laravel 5.3+

## Installation

1. Install the package via Composer:

```bash
composer require utkarshgayguwal/log-management
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag="log-management-config"
```

3. Publish and run migrations:

```bash
php artisan vendor:publish --tag="log-management-migrations"
php artisan migrate
```

4. Publish and run seeders (optional):

```bash
php artisan vendor:publish --tag="log-management-seeders"
php artisan db:seed --class=LogTemplateSeeder
php artisan db:seed --class=RedirectTemplateSeeder
```

5. Register the service provider in your `config/app.php`:

```php
'providers' => [
    // ...
    UtkarshGayguwal\LogManagement\Providers\LogManagementServiceProvider::class,
],
```

## Configuration

### Basic Configuration

After publishing the config file, you can customize the package behavior in `config/log-management.php`:

```php
return [
    // Define your module constants
    'modules' => [
        'user_management' => 1,
        'role_management' => 2,
        'content_management' => 3,
    ],

    // Define additional actions
    'actions' => [
        'approved' => 'approved',
        'rejected' => 'rejected',
        'exported' => 'exported',
    ],
];
```

### Module Constants

Define your application modules in the config file:

```php
'modules' => [
    'user_management' => 1,
    'role_management' => 2,
    'content_management' => 3,
    'settings' => 4,
],
```

Usage:
```php
use UtkarshGayguwal\LogManagement\Services\LoggerService;

$moduleId = config('log-management.modules.user_management');
$logger = app(LoggerService::class);
$logger->log($user, 'created', $moduleId, 'User Management', []);
```

## Usage

### Basic Logging

```php
use UtkarshGayguwal\LogManagement\Services\LoggerService;

$logger = app(LoggerService::class);

// Log a single action
$logger->log($user, 'created', 1, 'User Management', [
    'description' => 'User account created successfully',
    'ip_address' => '192.168.1.1'
]);
```

### Bulk Logging

```php
// Set up redirect configuration for bulk logging
$logger->setRedirection('user_profile', ['slug', 'id']);

// Log multiple users at once
$logger->logBulk(User::class, [1, 2, 3], 'updated', 1, 'User Management', [
    'description' => 'Bulk user update completed',
    'has_template' => true
]);
```

### Using HasLogs Trait

Add the trait to your models to easily retrieve logs:

```php
use UtkarshGayguwal\LogManagement\Traits\HasLogs;

class User extends Model
{
    use HasLogs;
}

// Get all logs for this user
$logs = $user->logs;

// Get logs by action
$createdLogs = $user->getLogsByAction('created');

// Get logs by module
$userManagementLogs = $user->getLogsByModule(1);
```

### API Endpoints

The package provides REST API endpoints for log management:

#### Get All Logs
```http
GET /api/logs?per_page=20&order_by=created_at&sort_by=desc
```

#### Filter Logs
```http
GET /api/logs?action=created&module_id=1&search=John
```

#### Date Range Filtering
```http
GET /api/logs?createdAt=2023-12-01 00:00,2023-12-31 23:59
```

#### Get Single Log
```http
GET /api/logs/{id}
```

### Filter Parameters

- `action`: Filter by log action (created, updated, deleted)
- `module_id`: Filter by module ID
- `log_type`: Filter by log type (user, system)
- `client_id`: Filter by client ID
- `search`: Search in title and description
- `created_by`: Filter by user who created the log
- `date`: Filter by specific date (Y-m-d)
- `createdAt`: Filter by date range
- `per_page`: Number of results per page (default: 20)
- `order_by`: Order column (default: id)
- `sort_by`: Sort direction (default: desc)

## Templates

### Log Templates

Log templates allow you to format log descriptions using variables:

```php
// Create a custom template in the database
LogTemplate::create([
    'name' => 'user_action',
    'body' => 'User {staff_name} {action} user {target_user} on {date} at {time}'
]);

// Use the template
$logger->log($user, 'created', 1, 'User Management', [
    'template_type' => 'user_action',
    'target_user' => 'John Doe',
    'staff_name' => 'Admin User'
]);
```

Available template variables:
- `{action}`: The action performed
- `{module_name}`: The module name
- `{date}`: Current date
- `{time}`: Current time
- `{staff_name}`: Current user name

### Redirect Templates

Redirect templates generate URLs for navigating back to related pages:

```php
// Create a redirect template
RedirectTemplate::create([
    'name' => 'user_profile',
    'path' => '/users/{id}/profile?prev_page=system-logs'
]);

// Use in logging
$logger->log($user, 'updated', 1, 'User Management', [
    'redirect_constant' => 'user_profile',
    'id' => $user->id
]);
```

## Advanced Usage

### Custom Actions

Extend the base actions with your own:

```php
// In your Log model
class Log extends Model
{
    // Basic CRUD actions are included:
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    
    // Add your custom actions:
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_EXPORTED = 'exported';
}
```

### Access Control

The package doesn't include built-in access control, but you can easily add your own:

```php
// In your controller
public function index(Request $request)
{
    $query = Log::query();
    
    // Add your access control logic
    if (!auth()->user()->isAdmin()) {
        $query->where('created_by', auth()->id());
    }
    
    return $query->paginate(20);
}
```

## Testing

Run the package tests:

```bash
composer test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## Changelog

### v1.0.0
- Initial release
- Core logging functionality
- Bulk logging support
- Template system
- API endpoints
- Advanced filtering

## License

This package is open-sourced software licensed under the MIT license.

## Support

If you encounter any issues or have questions, please open an issue on the GitHub repository.