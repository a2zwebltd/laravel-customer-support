<?php

namespace A2ZWeb\CustomerSupport\Nova\Actions;

use A2ZWeb\CustomerSupport\Actions\ChangeTicketStatus;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChangeStatusAction extends Action
{
    public $name = 'Change status';

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $next = TicketStatus::tryFrom((string) $fields->status);
        if (! $next) {
            return Action::danger('Invalid status.');
        }

        $action = app(ChangeTicketStatus::class);
        $userId = (int) auth()->id();

        foreach ($models as $ticket) {
            $action->execute($ticket, $next, $userId);
        }

        return Action::message('Updated '.$models->count().' ticket(s) to '.$next->label().'.');
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Status')
                ->options(TicketStatus::options())
                ->rules('required'),
        ];
    }
}
