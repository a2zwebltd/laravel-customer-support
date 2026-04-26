<div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6">
    <div class="flex flex-col gap-3">
        <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'index')" variant="subtle" icon="arrow-left" class="self-start" wire:navigate>
            Back to tickets
        </flux:button>
        <div>
            <flux:heading size="xl">Open a support ticket</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Tell us what's going on. Include any details, screenshots, or files that would help us understand the situation.
            </flux:text>
        </div>
    </div>

    @if ($errors->any())
        <flux:callout variant="danger" icon="exclamation-triangle">
            <ul class="list-inside list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout>
    @endif

    <form wire:submit="submit" class="relative flex flex-col gap-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700/80 dark:bg-zinc-900">
        <div class="absolute inset-x-0 top-0 h-0.5 bg-teal-500"></div>

        {{-- Category cards --}}
        <div>
            <flux:label class="mb-3">What is this about?</flux:label>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($categories as $key => $cat)
                    @php $selected = $category === $key; @endphp
                    <button
                        type="button"
                        wire:click="$set('category', '{{ $key }}')"
                        aria-pressed="{{ $selected ? 'true' : 'false' }}"
                        class="flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition-all
                            {{ $selected
                                ? 'border-teal-500 bg-teal-100 text-teal-900 ring-2 ring-teal-500/30 shadow-sm dark:border-teal-400 dark:bg-teal-900/40 dark:text-teal-50 dark:ring-teal-400/30'
                                : 'border-zinc-200 bg-white hover:border-teal-200 hover:bg-teal-50 dark:border-zinc-700/80 dark:bg-zinc-900 dark:hover:border-teal-500/40 dark:hover:bg-teal-950/30' }}"
                    >
                        <flux:icon name="{{ $cat['icon'] }}" class="size-6 {{ $selected ? 'text-teal-600 dark:text-teal-300' : 'text-zinc-400' }}" />
                        <span class="text-sm font-medium {{ $selected ? '' : 'text-zinc-700 dark:text-zinc-200' }}">{{ $cat['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <flux:input wire:model="subject" label="Subject" placeholder="Short summary, e.g. 'Cannot generate audit'" required />

        <flux:textarea wire:model="description" label="Tell us what's happening" rows="6" placeholder="What did you try? What did you expect? What did you see instead?" required />

        {{-- Priority --}}
        <div>
            <flux:label class="mb-2">Priority</flux:label>
            <flux:radio.group wire:model="priority" variant="segmented">
                @foreach ($priorities as $value => $label)
                    <flux:radio value="{{ $value }}" label="{{ $label }}" />
                @endforeach
            </flux:radio.group>
            <flux:text size="sm" class="mt-2 text-zinc-500 dark:text-zinc-400">
                Choose Urgent only for outages or business-critical blockers.
            </flux:text>
        </div>

        {{-- Attachments --}}
        <div>
            <flux:label class="mb-2">Attachments (optional)</flux:label>
            <label
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50/40 px-6 py-8 text-center transition hover:border-teal-300 hover:bg-teal-50/40 dark:border-zinc-700 dark:bg-zinc-800/40 dark:hover:border-teal-500/40 dark:hover:bg-teal-950/20"
                for="cs-attachments"
            >
                <flux:icon.paper-clip class="mb-2 size-6 text-zinc-400" />
                <span class="text-sm text-zinc-600 dark:text-zinc-300">
                    Drop files here or click to browse
                </span>
                <span class="mt-1 text-xs text-zinc-400">
                    Up to {{ number_format(((int) config('customer-support.attachments.max_size_kb', 10240)) / 1024, 1) }} MB per file
                </span>
                <input id="cs-attachments" type="file" wire:model="attachments" multiple class="hidden">
            </label>
            @if (! empty($attachments))
                <ul class="mt-3 space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    @foreach ($attachments as $file)
                        <li class="flex items-center gap-2">
                            <flux:icon.document class="size-4" />
                            <span>{{ $file->getClientOriginalName() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex items-center justify-end gap-2">
            <flux:button :href="route(config('customer-support.routes.name_prefix', 'support.').'index')" variant="ghost" wire:navigate>
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary" icon="paper-airplane">
                Submit ticket
            </flux:button>
        </div>
    </form>
</div>
