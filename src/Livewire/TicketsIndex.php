<?php

namespace A2ZWeb\CustomerSupport\Livewire;

use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'recent';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'priority', 'category']);
        $this->resetPage();
    }

    public function render(): View
    {
        $userId = (int) auth()->id();
        $isAgent = $this->isAgent();

        $query = SupportTicket::query()->with(['user', 'assignee']);

        if (! $isAgent) {
            $query->where('user_id', $userId);
        }

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('subject', 'like', $term)
                    ->orWhere('ticket_number', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->priority !== '') {
            $query->where('priority', $this->priority);
        }

        if ($this->category !== '') {
            $query->where('category', $this->category);
        }

        $query = match ($this->sort) {
            'oldest' => $query->orderBy('created_at'),
            'priority' => $query->orderByRaw("FIELD(priority,'urgent','high','normal','low')")->orderByDesc('created_at'),
            'overdue' => $query->orderByRaw('due_at IS NULL')->orderBy('due_at'),
            default => $query->orderByDesc('last_activity_at')->orderByDesc('created_at'),
        };

        return view('customer-support::livewire.tickets-index', [
            'tickets' => $query->paginate(12),
            'isAgent' => $isAgent,
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
            'categories' => TicketCategory::configured(),
            'accent' => config('customer-support.theme.accent', 'teal'),
        ])->layout(config('customer-support.layout', 'components.layouts.app'));
    }

    private function isAgent(): bool
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');

        return Gate::allows($gate);
    }
}
