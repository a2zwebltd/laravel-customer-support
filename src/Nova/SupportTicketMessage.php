<?php

namespace A2ZWeb\CustomerSupport\Nova;

use App\Nova\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class SupportTicketMessage extends Resource
{
    public static string $model = \A2ZWeb\CustomerSupport\Models\SupportTicketMessage::class;

    public static $title = 'id';

    public static $search = ['body'];

    public static $with = ['user', 'ticket'];

    public static function group(): string
    {
        return (string) (config('customer-support.nova.group') ?? 'Feedback');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Ticket', 'ticket', SupportTicket::class)->sortable(),
            BelongsTo::make('Author', 'user', $this->resolveUserResource())->searchable(),
            Textarea::make('Body')->rules('required')->alwaysShow(),
            Boolean::make('Internal note', 'is_internal_note')->sortable()->filterable(),
            DateTime::make('Sent at', 'created_at')->sortable()->readonly()->hideWhenCreating()->hideWhenUpdating(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [];
    }

    private function resolveUserResource(): string
    {
        return (string) (config('customer-support.nova.user_resource') ?? User::class);
    }
}
