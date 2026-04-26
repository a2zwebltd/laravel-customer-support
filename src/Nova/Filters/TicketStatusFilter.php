<?php

namespace A2ZWeb\CustomerSupport\Nova\Filters;

use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class TicketStatusFilter extends Filter
{
    public $name = 'Status';

    public function apply(NovaRequest $request, $query, $value)
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request): array
    {
        return collect(TicketStatus::options())
            ->mapWithKeys(fn ($label, $value) => [$label => $value])
            ->all();
    }
}
