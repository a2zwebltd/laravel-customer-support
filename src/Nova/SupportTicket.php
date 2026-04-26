<?php

namespace A2ZWeb\CustomerSupport\Nova;

use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Nova\Actions\AssignToMe;
use A2ZWeb\CustomerSupport\Nova\Actions\ChangeStatusAction;
use A2ZWeb\CustomerSupport\Nova\Filters\TicketPriorityFilter;
use A2ZWeb\CustomerSupport\Nova\Filters\TicketStatusFilter;
use A2ZWeb\CustomerSupport\Nova\Filters\UnassignedTicketsFilter;
use App\Nova\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class SupportTicket extends Resource
{
    public static string $model = \A2ZWeb\CustomerSupport\Models\SupportTicket::class;

    public static $title = 'subject';

    public static $search = ['id', 'ticket_number', 'subject', 'description'];

    public static $with = ['user', 'assignee'];

    public static function group(): string
    {
        return (string) (config('customer-support.nova.group') ?? 'Feedback');
    }

    public static function label(): string
    {
        return 'Support Tickets';
    }

    public static function singularLabel(): string
    {
        return 'Support Ticket';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Ticket #', 'ticket_number')
                ->sortable()
                ->copyable()
                ->exceptOnForms(),

            BelongsTo::make('Customer', 'user', $this->resolveUserResource())
                ->searchable()
                ->sortable()
                ->filterable(),

            BelongsTo::make('Assignee', 'assignee', $this->resolveUserResource())
                ->nullable()
                ->searchable()
                ->sortable(),

            Text::make('Subject')->rules('required', 'max:255')->sortable(),

            Textarea::make('Description')->rules('required')->alwaysShow(),

            Select::make('Category')
                ->options(collect(TicketCategory::configured())->mapWithKeys(fn ($v, $k) => [$k => $v['label']])->all())
                ->displayUsing(fn ($v) => TicketCategory::tryFrom((string) $v)?->label() ?? $v)
                ->sortable()
                ->filterable(),

            Select::make('Priority')
                ->options(TicketPriority::options())
                ->displayUsing(fn ($v) => TicketPriority::tryFrom((string) $v)?->label() ?? $v)
                ->sortable()
                ->filterable(),

            Select::make('Status')
                ->options(TicketStatus::options())
                ->displayUsing(fn ($v) => TicketStatus::tryFrom((string) $v)?->label() ?? $v)
                ->sortable()
                ->filterable(),

            DateTime::make('First response at')->onlyOnDetail()->readonly(),
            DateTime::make('Resolved at')->onlyOnDetail()->readonly(),
            DateTime::make('Closed at')->onlyOnDetail()->readonly(),
            DateTime::make('SLA due', 'due_at')->onlyOnDetail()->readonly(),
            DateTime::make('Last activity', 'last_activity_at')->onlyOnDetail()->readonly(),

            KeyValue::make('Metadata')->hideFromIndex()->nullable(),

            DateTime::make('Submitted', 'created_at')->sortable()->readonly()->hideWhenCreating()->hideWhenUpdating(),
            DateTime::make('Updated At')->readonly()->hideWhenCreating()->hideWhenUpdating()->hideFromIndex(),

            HasMany::make('Messages', 'messages', SupportTicketMessage::class),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new TicketStatusFilter,
            new TicketPriorityFilter,
            new UnassignedTicketsFilter,
        ];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new AssignToMe,
            new ChangeStatusAction,
        ];
    }

    private function resolveUserResource(): string
    {
        return (string) (config('customer-support.nova.user_resource') ?? User::class);
    }
}
