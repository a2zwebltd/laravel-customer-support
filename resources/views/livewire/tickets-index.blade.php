<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $isAgent ? 'Support Inbox' : 'Support Tickets' }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ $isAgent
                    ? 'Tickets submitted by users — assign, reply, and resolve.'
                    : "Track conversations with our support team." }}
            </flux:text>
        </div>
        <div class="flex items-center gap-2">
            @if ($isAgent)
                <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'admin.dashboard')" variant="subtle" icon="briefcase" wire:navigate>
                    Agent dashboard
                </flux:button>
            @endif
            <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'create')" variant="primary" icon="plus" wire:navigate>
                Open a ticket
            </flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" inline>
            {{ session('status') }}
        </flux:callout>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900 sm:flex-row sm:items-center">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by subject, ticket # or content…" clearable />
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-row sm:items-center">
            <flux:select wire:model.live="status" placeholder="All statuses">
                <flux:select.option value="">All statuses</flux:select.option>
                @foreach ($statusOptions as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="priority" placeholder="All priorities">
                <flux:select.option value="">All priorities</flux:select.option>
                @foreach ($priorityOptions as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="category" placeholder="All categories">
                <flux:select.option value="">All categories</flux:select.option>
                @foreach ($categories as $key => $cat)
                    <flux:select.option value="{{ $key }}">{{ $cat['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="sort">
                <flux:select.option value="recent">Most recent activity</flux:select.option>
                <flux:select.option value="oldest">Oldest first</flux:select.option>
                <flux:select.option value="priority">Highest priority</flux:select.option>
                <flux:select.option value="overdue">Overdue first</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Tickets --}}
    @if ($tickets->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-gradient-to-br from-zinc-50 via-white to-zinc-50 px-6 py-20 text-center dark:border-zinc-700/80 dark:from-zinc-800/80 dark:via-zinc-900 dark:to-zinc-800/80">
            <div class="mb-5 flex size-16 items-center justify-center rounded-2xl bg-teal-50 dark:bg-teal-900/20">
                <flux:icon.lifebuoy class="size-8 text-teal-500 dark:text-teal-400" />
            </div>
            <flux:heading size="lg" class="mb-2">No tickets yet</flux:heading>
            <flux:text class="mb-8 max-w-sm text-zinc-500 dark:text-zinc-400">
                {{ $isAgent
                    ? 'No tickets match your filters. New tickets will appear here as soon as they are submitted.'
                    : 'Open your first support ticket and a member of our team will get back to you shortly.' }}
            </flux:text>
            <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'create')" variant="primary" icon="plus" wire:navigate>
                Open a ticket
            </flux:button>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tickets as $ticket)
                @php
                    $statusColor = $ticket->status->color();
                    $priorityColor = $ticket->priority->color();
                    $isOverdue = $ticket->isOverdue();
                @endphp
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white transition-all hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-lg dark:border-zinc-700/80 dark:bg-zinc-900 dark:hover:border-teal-500/30">
                    <div class="absolute inset-x-0 top-0 h-0.5 bg-teal-500"></div>
                    <a href="{{ route(config('customer-support.routes.name_prefix', 'support.').'show', $ticket) }}" wire:navigate class="absolute inset-0 z-10" aria-label="Open ticket {{ $ticket->ticket_number }}"></a>

                    <div class="flex-1 p-5">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="font-mono text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $ticket->ticket_number }}</span>
                            <div class="flex items-center gap-1.5">
                                <flux:badge size="sm" color="{{ $priorityColor }}">{{ $ticket->priority->label() }}</flux:badge>
                                <flux:badge size="sm" color="{{ $statusColor }}">{{ $ticket->status->label() }}</flux:badge>
                            </div>
                        </div>

                        <h3 class="mb-2 line-clamp-2 text-base font-semibold text-zinc-900 dark:text-white">
                            {{ $ticket->subject }}
                        </h3>

                        <p class="line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $ticket->description }}
                        </p>

                        @if ($isAgent)
                            <div class="mt-4 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <flux:icon.user class="size-3.5" />
                                <span>{{ $ticket->user?->name ?? $ticket->user?->email ?? 'Unknown' }}</span>
                                @if ($ticket->assignee)
                                    <span class="text-zinc-300 dark:text-zinc-600">•</span>
                                    <flux:icon.user-plus class="size-3.5 text-teal-500" />
                                    <span>{{ $ticket->assignee->name ?? $ticket->assignee->email }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($isOverdue)
                        <div class="border-t border-red-100 bg-red-50/60 px-5 py-2 text-xs font-medium text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-300">
                            <div class="flex items-center gap-1.5">
                                <flux:icon.exclamation-triangle class="size-3.5" />
                                SLA breached
                            </div>
                        </div>
                    @endif

                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-zinc-100 bg-zinc-50/50 px-5 py-2.5 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/30 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1">
                            <flux:icon.tag class="size-3.5" />
                            {{ $ticket->category->label() }}
                        </span>
                        <span>{{ optional($ticket->last_activity_at ?? $ticket->created_at)->diffForHumans() }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
