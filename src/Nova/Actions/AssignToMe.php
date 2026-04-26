<?php

namespace A2ZWeb\CustomerSupport\Nova\Actions;

use A2ZWeb\CustomerSupport\Actions\AssignTicket;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class AssignToMe extends Action
{
    public $name = 'Assign to me';

    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $userId = (int) auth()->id();
        $action = app(AssignTicket::class);

        foreach ($models as $ticket) {
            $action->execute($ticket, $userId);
        }

        return Action::message('Assigned '.$models->count().' ticket(s) to you.');
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
