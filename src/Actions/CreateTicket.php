<?php

namespace A2ZWeb\CustomerSupport\Actions;

use A2ZWeb\CustomerSupport\DataTransferObjects\TicketData;
use A2ZWeb\CustomerSupport\Events\TicketCreated;
use A2ZWeb\CustomerSupport\Mail\TicketCreatedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateTicket
{
    public function execute(TicketData $data): SupportTicket
    {
        return DB::transaction(function () use ($data) {
            $ticket = SupportTicket::create([
                'user_id' => $data->userId,
                'subject' => $data->subject,
                'description' => $data->description,
                'category' => $data->category->value,
                'priority' => $data->priority->value,
                'metadata' => $data->metadata,
            ]);

            $message = SupportTicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $data->userId,
                'body' => $data->description,
                'is_internal_note' => false,
            ]);

            $this->attachFiles($message, $data->attachments);

            $ticket->refresh();

            TicketCreated::dispatch($ticket);

            $this->sendNotifications($ticket);

            return $ticket;
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function attachFiles($model, array $files): void
    {
        if (! config('customer-support.attachments.enabled', true)) {
            return;
        }
        if (empty($files)) {
            return;
        }

        $collection = config('customer-support.attachments.collection', 'attachments');

        foreach ($files as $file) {
            $this->addAttachment($model, $file, $collection);
        }
    }

    /**
     * Attach a single uploaded file to the model, working regardless of which
     * disk Livewire used for the temporary upload (local in dev, S3 in prod).
     *
     * Livewire's TemporaryUploadedFile may live on a remote disk, so we read
     * its bytes through Livewire's own storage and hand a string to Media
     * Library — never letting Media Library resolve a (missing) local path.
     */
    private function addAttachment(mixed $model, mixed $file, string $collection): void
    {
        if ($file instanceof TemporaryUploadedFile) {
            $model->addMediaFromString($file->get())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection($collection);

            return;
        }

        if ($file instanceof UploadedFile) {
            $model->addMedia($file)->toMediaCollection($collection);
        }
    }

    private function sendNotifications(SupportTicket $ticket): void
    {
        if (! config('customer-support.mail.notifications.ticket_created', true)) {
            return;
        }

        $customerEmail = optional($ticket->user)->email;
        if ($customerEmail) {
            Mail::to($customerEmail)->queue(new TicketCreatedMail($ticket, forAgent: false));
        }

        $admins = (array) config('customer-support.mail.admin_recipients', []);
        if (! empty($admins)) {
            Mail::to($admins)->queue(new TicketCreatedMail($ticket, forAgent: true));
        }
    }
}
