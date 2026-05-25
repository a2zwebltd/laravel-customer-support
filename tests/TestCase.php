<?php

namespace A2ZWeb\CustomerSupport\Tests;

use A2ZWeb\CustomerSupport\CustomerSupportServiceProvider;
use Illuminate\Support\Facades\Gate;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            CustomerSupportServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('customer-support.user_model', User::class);
        $app['config']->set('customer-support.mail.admin_recipients', ['agents@example.com']);
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => sys_get_temp_dir().'/customer-support-test',
            'visibility' => 'public',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-support-tickets', fn ($user) => (bool) ($user->is_admin ?? false));
    }
}
