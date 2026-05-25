<?php

namespace A2ZWeb\CustomerSupport\Livewire;

use A2ZWeb\CustomerSupport\Actions\CreateTicket;
use A2ZWeb\CustomerSupport\DataTransferObjects\TicketData;
use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class TicketCreate extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:500')]
    public string $subject = '';

    #[Validate('required|string|min:10|max:10000')]
    public string $description = '';

    #[Validate('required|string')]
    public string $category = '';

    #[Validate('required|string')]
    public string $priority = TicketPriority::Normal->value;

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

    public function submit(CreateTicket $action): void
    {
        $this->validate();

        $category = TicketCategory::tryFrom($this->category) ?? TicketCategory::Other;
        $priority = TicketPriority::tryFrom($this->priority) ?? TicketPriority::Normal;

        $ticket = $action->execute(new TicketData(
            userId: (int) auth()->id(),
            subject: $this->subject,
            description: $this->description,
            category: $category,
            priority: $priority,
            attachments: array_map(fn ($u) => $u, $this->attachments),
        ));

        session()->flash('status', "Ticket {$ticket->ticket_number} created — we'll be in touch soon.");

        $this->redirectRoute(config('customer-support.routes.name_prefix', 'support.').'show', ['ticket' => $ticket->ticket_number], navigate: true);
    }

    public function render(): View
    {
        return view('customer-support::livewire.ticket-create', [
            'categories' => TicketCategory::configured(),
            'priorities' => TicketPriority::options(),
            'accent' => config('customer-support.theme.accent', 'teal'),
        ])->layout(config('customer-support.layout', 'components.layouts.app'));
    }
}
