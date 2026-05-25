<?php

namespace A2ZWeb\CustomerSupport\Livewire;

use A2ZWeb\CustomerSupport\Actions\AssignTicket;
use A2ZWeb\CustomerSupport\Actions\ChangeTicketStatus;
use A2ZWeb\CustomerSupport\Actions\ReplyToTicket;
use A2ZWeb\CustomerSupport\DataTransferObjects\MessageData;
use A2ZWeb\CustomerSupport\DataTransferObjects\SlaTracking;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class TicketShow extends Component
{
    use WithFileUploads;

    public SupportTicket $ticket;

    #[Validate('required|string|min:1|max:10000')]
    public string $reply = '';

    public bool $internal = false;

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    /**
     * Staging slot for a single file. Uploaded one at a time and appended to
     * $attachments so the input never needs the `multiple` attribute — which
     * Livewire's S3 temporary-upload driver does not support.
     *
     * @var TemporaryUploadedFile|null
     */
    public $attachment = null;

    public function mount(SupportTicket $ticket): void
    {
        $this->ticket = $ticket->load(['user', 'assignee', 'messages.user', 'messages.media', 'media']);

        $this->authorize('view', $this->ticket);
    }

    public function rules(): array
    {
        $maxKb = (int) config('customer-support.attachments.max_size_kb', 10240);
        $mimes = (array) config('customer-support.attachments.accepted_mimes', []);

        return [
            'attachments.*' => ['file', 'max:'.$maxKb, 'mimetypes:'.implode(',', $mimes)],
        ];
    }

    public function updatedAttachment(): void
    {
        $maxKb = (int) config('customer-support.attachments.max_size_kb', 10240);
        $mimes = (array) config('customer-support.attachments.accepted_mimes', []);

        $this->validate([
            'attachment' => ['file', 'max:'.$maxKb, 'mimetypes:'.implode(',', $mimes)],
        ]);

        if ($this->attachment) {
            $this->attachments[] = $this->attachment;
        }

        $this->attachment = null;
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function postReply(ReplyToTicket $action): void
    {
        $this->authorize('reply', $this->ticket);

        if ($this->internal) {
            $this->authorize('addInternalNote', $this->ticket);
        }

        $this->validate();

        $action->execute(new MessageData(
            ticketId: $this->ticket->id,
            userId: (int) auth()->id(),
            body: $this->reply,
            isInternalNote: $this->internal,
            attachments: array_map(fn ($u) => $u, $this->attachments),
        ));

        $this->reset(['reply', 'attachment', 'attachments', 'internal']);
        $this->ticket->refresh();
        $this->ticket->load(['user', 'assignee', 'messages.user', 'messages.media']);

        $this->dispatch('ticket-updated');
    }

    public function changeStatus(string $status, ChangeTicketStatus $action): void
    {
        $this->authorize('changeStatus', $this->ticket);

        $next = TicketStatus::tryFrom($status);
        if (! $next) {
            return;
        }

        $action->execute($this->ticket, $next, (int) auth()->id());
        $this->ticket->refresh();
    }

    public function toggleAssignToMe(AssignTicket $action): void
    {
        $this->authorize('assign', $this->ticket);

        $userId = (int) auth()->id();
        $next = $this->ticket->assigned_to === $userId ? null : $userId;
        $action->execute($this->ticket, $next);
        $this->ticket->refresh();
    }

    public function render(): View
    {
        $isAgent = Gate::allows(config('customer-support.admin_gate', 'manage-support-tickets'));

        $messages = $isAgent
            ? $this->ticket->messages
            : $this->ticket->messages->reject(fn ($m) => $m->is_internal_note);

        return view('customer-support::livewire.ticket-show', [
            'isAgent' => $isAgent,
            'messages' => $messages,
            'sla' => SlaTracking::forTicket($this->ticket),
            'statusOptions' => TicketStatus::options(),
            'accent' => config('customer-support.theme.accent', 'teal'),
        ])->layout(config('customer-support.layout', 'components.layouts.app'));
    }
}
