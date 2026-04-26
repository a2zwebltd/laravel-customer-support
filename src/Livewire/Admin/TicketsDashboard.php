<?php

namespace A2ZWeb\CustomerSupport\Livewire\Admin;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TicketsDashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'all';

    public function mount(): void
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');
        if (! Gate::allows($gate)) {
            abort(403);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['all', 'unassigned', 'mine', 'overdue'], true) ? $tab : 'all';
        $this->resetPage();
    }

    public function render(): View
    {
        $query = SupportTicket::query()->with(['user', 'assignee']);

        $userId = (int) auth()->id();

        $query = match ($this->tab) {
            'unassigned' => $query->open()->unassigned(),
            'mine' => $query->open()->where('assigned_to', $userId),
            'overdue' => $query->overdue(),
            default => $query,
        };

        return view('customer-support::livewire.admin.tickets-dashboard', [
            'tickets' => $query->orderByDesc('last_activity_at')->paginate(20),
            'counts' => $this->counts($userId),
            'accent' => config('customer-support.theme.accent', 'teal'),
        ])->layout(config('customer-support.layout', 'components.layouts.app'));
    }

    /**
     * @return array<string, int>
     */
    private function counts(int $userId): array
    {
        return [
            'all' => SupportTicket::query()->count(),
            'unassigned' => SupportTicket::query()->open()->unassigned()->count(),
            'mine' => SupportTicket::query()->open()->where('assigned_to', $userId)->count(),
            'overdue' => SupportTicket::query()->overdue()->count(),
        ];
    }
}
