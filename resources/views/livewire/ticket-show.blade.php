<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-3">
        <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'index')" variant="subtle" icon="arrow-left" class="self-start" wire:navigate>
            Back to tickets
        </flux:button>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="font-mono text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $ticket->ticket_number }}</span>
                    <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                    <flux:badge size="sm" color="{{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</flux:badge>
                    <flux:badge size="sm" color="zinc">{{ $ticket->category->label() }}</flux:badge>
                </div>
                <flux:heading size="xl">{{ $ticket->subject }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    Opened by {{ $ticket->user?->name ?? $ticket->user?->email ?? 'unknown' }} · {{ optional($ticket->created_at)->diffForHumans() }}
                </flux:text>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        {{-- Timeline --}}
        <div class="relative flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900">
            <div class="absolute inset-x-0 top-0 h-0.5 rounded-t-2xl bg-teal-500"></div>

            @foreach ($messages as $msg)
                @php
                    $isAgentMessage = $msg->isFromAgent();
                    $alignRight = $isAgentMessage;
                    $bubbleClasses = $msg->is_internal_note
                        ? 'bg-amber-50 border-amber-200 dark:bg-amber-950/30 dark:border-amber-700/40'
                        : ($isAgentMessage
                            ? 'bg-teal-50 border-teal-100 dark:bg-teal-950/30 dark:border-teal-700/40'
                            : 'bg-zinc-50 border-zinc-200 dark:bg-zinc-800/40 dark:border-zinc-700/60');
                @endphp
                <div class="flex {{ $alignRight ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] rounded-2xl border px-4 py-3 {{ $bubbleClasses }}">
                        <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">
                                {{ $msg->user?->name ?? $msg->user?->email ?? 'Unknown' }}
                                @if ($isAgentMessage)
                                    <span class="ml-1 rounded-full bg-teal-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Agent</span>
                                @endif
                                @if ($msg->is_internal_note)
                                    <span class="ml-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Internal</span>
                                @endif
                            </span>
                            <span class="text-zinc-400">{{ optional($msg->created_at)->diffForHumans() }}</span>
                        </div>
                        <div class="whitespace-pre-wrap text-sm text-zinc-800 dark:text-zinc-100">{{ $msg->body }}</div>
                        @if ($msg->getMedia(config('customer-support.attachments.collection', 'attachments'))->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($msg->getMedia(config('customer-support.attachments.collection', 'attachments')) as $media)
                                    <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-2.5 py-1 text-xs text-zinc-700 hover:border-teal-300 hover:text-teal-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        <flux:icon.paper-clip class="size-3.5" />
                                        <span>{{ $media->file_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Reply box --}}
            @if (! $ticket->status->isClosed() || $isAgent)
                <form wire:submit="postReply" class="mt-2 flex flex-col gap-3 rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700/80 dark:bg-zinc-800/30">
                    <flux:textarea wire:model="reply" rows="4" placeholder="Write a reply…" />
                    @if (! empty($attachments))
                        <ul class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                            @foreach ($attachments as $i => $file)
                                <li class="flex items-center gap-2">
                                    <flux:icon.document class="size-3.5" />
                                    <span>{{ $file->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeAttachment({{ $i }})" class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400" aria-label="Remove attachment">
                                        <flux:icon.x-mark class="size-3.5" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-600 hover:border-teal-300 hover:text-teal-700 dark:border-zinc-700 dark:text-zinc-300">
                                <flux:icon.paper-clip class="size-4" wire:loading.remove wire:target="attachment" />
                                <flux:icon.arrow-path class="size-4 animate-spin" wire:loading wire:target="attachment" />
                                Attach
                                <input type="file" wire:model="attachment" class="hidden">
                            </label>
                            @if ($isAgent)
                                <flux:checkbox wire:model="internal" label="Internal note (not visible to customer)" />
                            @endif
                        </div>
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            {{ $internal ? 'Add internal note' : 'Send reply' }}
                        </flux:button>
                    </div>
                </form>
            @else
                <flux:callout variant="secondary" icon="lock-closed" inline>
                    This ticket is closed. Reach out by opening a new ticket.
                </flux:callout>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="flex flex-col gap-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">Ticket details</flux:heading>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Status</dt>
                        <dd>
                            @if ($isAgent)
                                <flux:dropdown>
                                    <flux:button size="xs" variant="ghost" icon-trailing="chevron-down">
                                        <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                                    </flux:button>
                                    <flux:menu>
                                        @foreach ($statusOptions as $value => $label)
                                            <flux:menu.item wire:click="changeStatus('{{ $value }}')">{{ $label }}</flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <flux:badge size="sm" color="{{ $ticket->status->color() }}">{{ $ticket->status->label() }}</flux:badge>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Priority</dt>
                        <dd><flux:badge size="sm" color="{{ $ticket->priority->color() }}">{{ $ticket->priority->label() }}</flux:badge></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Category</dt>
                        <dd class="text-zinc-700 dark:text-zinc-200">{{ $ticket->category->label() }}</dd>
                    </div>
                    @if ($isAgent)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-zinc-500 dark:text-zinc-400">Assignee</dt>
                            <dd class="text-zinc-700 dark:text-zinc-200">
                                @if ($ticket->assignee)
                                    {{ $ticket->assignee->name ?? $ticket->assignee->email }}
                                @else
                                    <span class="text-zinc-400">Unassigned</span>
                                @endif
                            </dd>
                        </div>
                        <div class="pt-1">
                            <flux:button size="xs" variant="subtle" icon="user-plus" wire:click="toggleAssignToMe" class="w-full">
                                {{ $ticket->assigned_to === auth()->id() ? 'Unassign me' : 'Assign to me' }}
                            </flux:button>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Opened</dt>
                        <dd class="text-zinc-700 dark:text-zinc-200">{{ optional($ticket->created_at)->format('M j, Y · H:i') }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-zinc-500 dark:text-zinc-400">Last activity</dt>
                        <dd class="text-zinc-700 dark:text-zinc-200">{{ optional($ticket->last_activity_at ?? $ticket->updated_at)->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            {{-- SLA panel --}}
            @if ($sla->dueAt)
                <div class="rounded-2xl border p-5 shadow-sm
                    {{ $sla->breached
                        ? 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30'
                        : 'border-teal-200 bg-teal-50 dark:border-teal-700/40 dark:bg-teal-950/20' }}">
                    <div class="mb-2 flex items-center gap-2">
                        @if ($sla->breached)
                            <flux:icon.exclamation-triangle class="size-4 text-red-500" />
                            <flux:heading size="sm" class="!text-red-700 dark:!text-red-300">SLA breached</flux:heading>
                        @else
                            <flux:icon.clock class="size-4 text-teal-600 dark:text-teal-400" />
                            <flux:heading size="sm">SLA</flux:heading>
                        @endif
                    </div>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">
                        {{ $sla->humanLabel() }} · due {{ $sla->dueAt->format('M j, H:i') }}
                    </flux:text>
                </div>
            @endif
        </aside>
    </div>
</div>
