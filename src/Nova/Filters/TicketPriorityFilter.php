<?php

namespace A2ZWeb\CustomerSupport\Nova\Filters;

use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class TicketPriorityFilter extends Filter
{
    public $name = 'Priority';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('priority', $value);
    }

    public function options(NovaRequest $request): array
    {
        return collect(TicketPriority::options())
            ->mapWithKeys(fn ($label, $value) => [$label => $value])
            ->all();
    }
}
