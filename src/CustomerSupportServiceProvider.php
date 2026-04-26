<?php

namespace A2ZWeb\CustomerSupport;

use A2ZWeb\CustomerSupport\Console\Commands\EscalateOverdueTickets;
use A2ZWeb\CustomerSupport\Livewire\Admin\TicketsDashboard;
use A2ZWeb\CustomerSupport\Livewire\TicketCreate;
use A2ZWeb\CustomerSupport\Livewire\TicketShow;
use A2ZWeb\CustomerSupport\Livewire\TicketsIndex;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use A2ZWeb\CustomerSupport\Policies\SupportTicketMessagePolicy;
use A2ZWeb\CustomerSupport\Policies\SupportTicketPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Livewire\Livewire;

class CustomerSupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/customer-support.php', 'customer-support');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerBladeComponents();
        $this->registerPolicies();
        $this->registerRoutes();
        $this->registerLivewire();
        $this->registerNova();
        $this->registerCommands();
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/customer-support.php' => config_path('customer-support.php'),
        ], 'customer-support-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/customer-support'),
        ], 'customer-support-views');
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'customer-support');
    }

    private function registerBladeComponents(): void
    {
        Blade::componentNamespace('A2ZWeb\\CustomerSupport\\View\\Components', 'customer-support');
    }

    private function registerPolicies(): void
    {
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);
        Gate::policy(SupportTicketMessage::class, SupportTicketMessagePolicy::class);
    }

    private function registerRoutes(): void
    {
        if (! config('customer-support.routes.enabled', true)) {
            return;
        }

        Route::group([
            'prefix' => config('customer-support.routes.prefix', 'support'),
            'as' => config('customer-support.routes.name_prefix', 'support.'),
            'middleware' => config('customer-support.routes.middleware', ['web', 'auth']),
        ], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    private function registerLivewire(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        $this->callAfterResolving('livewire', function ($livewire): void {
            $livewire->component('customer-support.tickets-index', TicketsIndex::class);
            $livewire->component('customer-support.ticket-create', TicketCreate::class);
            $livewire->component('customer-support.ticket-show', TicketShow::class);
            $livewire->component('customer-support.admin-tickets-dashboard', TicketsDashboard::class);
        });
    }

    private function registerNova(): void
    {
        if (! config('customer-support.nova.register_resources', true)) {
            return;
        }

        if (! class_exists(Nova::class)) {
            return;
        }

        Nova::resources([
            \A2ZWeb\CustomerSupport\Nova\SupportTicket::class,
            \A2ZWeb\CustomerSupport\Nova\SupportTicketMessage::class,
        ]);
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            EscalateOverdueTickets::class,
        ]);
    }
}
