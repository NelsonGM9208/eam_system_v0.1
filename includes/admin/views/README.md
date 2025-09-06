# Users Table View Architecture

## Overview
This directory contains reusable view components for the admin panel, specifically designed to eliminate code duplication and provide consistent user interfaces across different user management pages.

## Files Structure

### `users_table_view.php` - Main Reusable Component
This is the core reusable table view that can be configured for different use cases:
- **All Users** (users.php)
- **Pending Users** (pending_users.php)
- **Any filtered user list**

## Configuration Options

The `$tableConfig` array controls the behavior and appearance of the table:

```php
$tableConfig = [
    'title' => 'Page Title',                    // Table header title
    'showCheckboxes' => true/false,             // Show selection checkboxes
    'showStatus' => true/false,                 // Show status column
    'showVerification' => true/false,           // Show verification column
    'showRegistrationDate' => true/false,       // Show registration date column
    'actions' => ['view', 'edit', 'delete'],    // Available action buttons
    'bulkActions' => true/false,                // Enable bulk operations
    'searchPlaceholder' => 'Search text...',    // Search input placeholder
    'emptyMessage' => 'No data message',        // Message when no users found
    'paginationUrl' => '?page=page&num='       // Pagination URL pattern
];
```

## Available Actions

The following actions can be configured:
- `'view'` - View user details
- `'edit'` - Edit user information
- `'delete'` - Remove user
- `'approve'` - Approve pending user
- `'reject'` - Reject pending user

## Usage Examples

### For Pending Users (pending_users.php)
```php
$tableConfig = [
    'title' => 'Pending Users Approval',
    'showCheckboxes' => true,
    'showStatus' => true,
    'showVerification' => true,
    'showRegistrationDate' => true,
    'actions' => ['view', 'approve', 'reject'],
    'bulkActions' => true,
    'searchPlaceholder' => 'Search pending users...',
    'emptyMessage' => 'No pending users found. All users have been processed.',
    'paginationUrl' => '?page=pending_users&page_num='
];
```

### For All Users (users.php)
```php
$tableConfig = [
    'title' => 'Users Management',
    'showCheckboxes' => false,
    'showStatus' => true,
    'showVerification' => true,
    'showRegistrationDate' => false,
    'actions' => ['view', 'edit', 'delete'],
    'bulkActions' => false,
    'searchPlaceholder' => 'Search users...',
    'emptyMessage' => 'No users found.',
    'paginationUrl' => '?page=users&page_num='
];
```

## Required Variables

Before including the view, ensure these variables are set:
- `$users` - MySQL result set of users
- `$totalUsersCount` - Total number of users
- `$limit` - Users per page
- `$page` - Current page number
- `$offset` - Offset for pagination

## Benefits

1. **Code Reusability** - Single table structure for multiple use cases
2. **Consistency** - Uniform appearance across all user tables
3. **Maintainability** - Changes to table structure only need to be made in one place
4. **Flexibility** - Easy to configure for different requirements
5. **Scalability** - Simple to add new user management pages

## Adding New User Management Pages

1. Create a new PHP file (e.g., `active_users.php`)
2. Set up your database queries and variables
3. Configure `$tableConfig` for your specific needs
4. Include the view: `include __DIR__ . "/views/users_table_view.php";`

## Styling

The table uses Bootstrap classes and maintains the same visual style as the original tables:
- Responsive design
- Hover effects
- Consistent badge colors for roles, status, and verification
- Professional card layout
- Mobile-friendly pagination

