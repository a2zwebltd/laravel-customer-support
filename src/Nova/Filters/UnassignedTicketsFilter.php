<?php

namespace A2ZWeb\CustomerSupport\Nova\Filters;

use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Http\Requests\NovaRequest;

class UnassignedTicketsFilter extends BooleanFilter
{
    public $name = 'Assignment';

    public function apply(NovaRequest $request, $query, $value)
    {
        if (! empty($value['unassigned'])) {
            $query->whereNull('assigned_to');
        }
        if (! empty($value['assigned'])) {
            $query->whereNotNull('assigned_to');
        }

        return $query;
    }

    public function options(NovaRequest $request): array
    {
        return [
            'Unassigned only' => 'unassigned',
            'Assigned only' => 'assigned',
        ];
    }
}
