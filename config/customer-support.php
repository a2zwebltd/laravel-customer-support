<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used for ticket authors, agents, and assignees.
    | Defaults to the application's auth provider model.
    |
    */
    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'prefix' => '',
        'tickets' => 'support_tickets',
        'messages' => 'support_ticket_messages',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'support',
        'name_prefix' => 'support.',
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Gate
    |--------------------------------------------------------------------------
    |
    | Gate name used to decide whether a user can manage tickets as an agent
    | (assign, change status, add internal notes, view all tickets).
    | Define it in your AppServiceProvider, e.g.:
    |
    |   Gate::define('manage-support-tickets', fn ($user) => $user->is_admin);
    |
    */
    'admin_gate' => 'manage-support-tickets',

    /*
    |--------------------------------------------------------------------------
    | Ticket Numbering
    |--------------------------------------------------------------------------
    */
    'ticket_number' => [
        'prefix' => 'CS-',
        'pad' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Override here to customise the categories shown on the ticket form.
    | Keys are persisted to the database; labels are user-facing.
    |
    */
    'categories' => [
        'bug' => ['label' => 'Bug Report', 'icon' => 'bug-ant'],
        'question' => ['label' => 'Question', 'icon' => 'question-mark-circle'],
        'billing' => ['label' => 'Billing & Payments', 'icon' => 'credit-card'],
        'feature_request' => ['label' => 'Feature Request', 'icon' => 'sparkles'],
        'account' => ['label' => 'Account & Access', 'icon' => 'user-circle'],
        'other' => ['label' => 'Other', 'icon' => 'ellipsis-horizontal'],
    ],

    /*
    |--------------------------------------------------------------------------
    | SLA (in hours) per priority level
    |--------------------------------------------------------------------------
    */
    'sla_hours' => [
        'urgent' => 4,
        'high' => 12,
        'normal' => 48,
        'low' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Notifications
    |--------------------------------------------------------------------------
    */
    'mail' => [
        'from' => [
            'address' => env('SUPPORT_MAIL_FROM_ADDRESS'),
            'name' => env('SUPPORT_MAIL_FROM_NAME'),
        ],
        'admin_recipients' => array_filter(array_map(
            'trim',
            explode(',', (string) env('SUPPORT_ADMIN_RECIPIENTS', ''))
        )),
        'notifications' => [
            'ticket_created' => true,
            'ticket_replied' => true,
            'status_changed' => true,
            'ticket_resolved' => true,
            'ticket_overdue' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachments (spatie/laravel-medialibrary)
    |--------------------------------------------------------------------------
    */
    'attachments' => [
        'enabled' => true,
        'disk' => 'public',
        'collection' => 'attachments',
        'max_size_kb' => 10240,
        'accepted_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'text/csv',
            'application/zip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova
    |--------------------------------------------------------------------------
    */
    'nova' => [
        'register_resources' => true,
        'group' => 'Feedback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | Tailwind colour token used for the accent (top border, focused button).
    | Pick any Tailwind colour family available in your host CSS (teal, blue,
    | purple, emerald, amber, rose, ...).
    |
    */
    'theme' => [
        'accent' => 'teal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Layout
    |--------------------------------------------------------------------------
    |
    | Blade layout used by the package's Livewire pages. Defaults to the
    | Livewire convention `components.layouts.app`, override here (or via
    | the CUSTOMER_SUPPORT_LAYOUT env var) to match your host application
    | (e.g. `layouts.app` if you use a Blade layout file at
    | `resources/views/layouts/app.blade.php`).
    |
    */
    'layout' => env('CUSTOMER_SUPPORT_LAYOUT', 'components.layouts.app'),
];
