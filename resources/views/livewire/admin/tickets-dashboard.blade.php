<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">Agent dashboard</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Triage and respond to support tickets across the queue.
            </flux:text>
        </div>
        <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'index')" variant="subtle" icon="inbox" wire:navigate>
            Customer view
        </flux:button>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900">
        @php
            $tabs = [
                'all' => ['label' => 'All', 'icon' => 'queue-list'],
                'unassigned' => ['label' => 'Unassigned', 'icon' => 'user-minus'],
                'mine' => ['label' => 'Assigned to me', 'icon' => 'user'],
                'overdue' => ['label' => 'Overdue', 'icon' => 'exclamation-triangle'],
            ];
        @endphp
        @foreach ($tabs as $key => $meta)
            <button
                type="button"
                wire:click="setTab('{{ $key }}')"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition
                    {{ $tab === $key
                        ? 'bg-teal-500 text-white shadow-sm'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}"
            >
                <flux:icon name="{{ $meta['icon'] }}" class="size-4" />
                <span>{{ $meta['label'] }}</span>
                <span class="ml-1 rounded-full {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }} px-1.5 text-xs">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </button>
        @endforeach
    </div>

    @if ($tickets->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-gradient-to-br from-zinc-50 via-white to-zinc-50 px-6 py-16 text-center dark:border-zinc-700/80 dark:from-zinc-800/80 dark:via-zinc-900 dark:to-zinc-800/80">
            <div class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-teal-50 dark:bg-teal-900/20">
                <flux:icon.check-circle class="size-7 text-teal-500" />
            </div>
            <flux:heading size="lg" class="mb-1">All clear</flux:heading>
            <flux:text class="max-w-sm text-zinc-500 dark:text-zinc-400">No tickets in this queue.</flux:text>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white px-4 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Ticket</flux:table.column>
                    <flux:table.column>Customer</flux:table.column>
                    <flux:table.column>Priority</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Assignee</flux:table.column>
                    <flux:table.column>SLA</flux:table.column>
                    <flux:table.column>Last activity</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($tickets as $ticket)
                        <flux:table.row :href="route(config('customer-support.routes.name_prefix', 'support.').'show', $ticket)" wire:navigate>
                            <flux:table.cell class="align-middle">
                                <div class="flex flex-col leading-tight">
                                    <span class="font-mono text-xs text-zinc-500">{{ $ticket->ticket_number }}</span>
                                    <span class="font-medium text-zinc-900 dark:text-white line-clamp-1 max-w-xs">{{ $ticket->subject }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="align-middle">{{ $ticket->user?->name ?? $ticket->user?->email ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="align-middle">
                                <flux:badge size="sm" color="{{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="align-middle">
                                <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="align-middle">
                                @if ($ticket->assignee)
                                    {{ $ticket->assignee->name ?? $ticket->assignee->email }}
                                @else
                                    <span class="text-zinc-400">Unassigned</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="align-middle">
                                @if ($ticket->due_at && $ticket->status->isOpen())
                                    @if ($ticket->isOverdue())
                                        <span class="text-red-600 dark:text-red-400">Overdue</span>
                                    @else
                                        {{ $ticket->due_at->diffForHumans() }}
                                    @endif
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="align-middle">{{ optional($ticket->last_activity_at ?? $ticket->updated_at)->diffForHumans() }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
        <div>{{ $tickets->links() }}</div>
    @endif
</div>
