<?php

namespace A2ZWeb\CustomerSupport\Actions;

use A2ZWeb\CustomerSupport\DataTransferObjects\MessageData;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use A2ZWeb\CustomerSupport\Events\TicketReplied;
use A2ZWeb\CustomerSupport\Mail\TicketRepliedMail;
use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ReplyToTicket
{
    public function execute(MessageData $data): SupportTicketMessage
    {
        return DB::transaction(function () use ($data) {
            /** @var SupportTicket $ticket */
            $ticket = SupportTicket::query()->findOrFail($data->ticketId);

            $message = SupportTicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $data->userId,
                'body' => $data->body,
                'is_internal_note' => $data->isInternalNote,
            ]);

            $this->attachFiles($message, $data->attachments);

            $authorIsAgent = $this->isAgent($message);

            if ($authorIsAgent && ! $ticket->first_response_at && ! $data->isInternalNote) {
                $ticket->first_response_at = now();
            }

            if (! $data->isInternalNote) {
                $ticket->status = $authorIsAgent
                    ? TicketStatus::AwaitingCustomer
                    : TicketStatus::Pending;
            }

            $ticket->last_activity_at = now();
            $ticket->save();

            TicketReplied::dispatch($ticket, $message);

            if (! $data->isInternalNote) {
                $this->sendNotifications($ticket, $message, $authorIsAgent);
            }

            return $message;
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function attachFiles(SupportTicketMessage $message, array $files): void
    {
        if (! config('customer-support.attachments.enabled', true)) {
            return;
        }
        if (empty($files)) {
            return;
        }

        $collection = config('customer-support.attachments.collection', 'attachments');

        foreach ($files as $file) {
            $this->addAttachment($message, $file, $collection);
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
    private function addAttachment(SupportTicketMessage $message, mixed $file, string $collection): void
    {
        if ($file instanceof TemporaryUploadedFile) {
            $message->addMediaFromString($file->get())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection($collection);

            return;
        }

        if ($file instanceof UploadedFile) {
            $message->addMedia($file)->toMediaCollection($collection);
        }
    }

    private function isAgent(SupportTicketMessage $message): bool
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');
        $author = $message->user;
        if (! $author) {
            return false;
        }

        return Gate::forUser($author)->allows($gate);
    }

    private function sendNotifications(SupportTicket $ticket, SupportTicketMessage $message, bool $fromAgent): void
    {
        if (! config('customer-support.mail.notifications.ticket_replied', true)) {
            return;
        }

        if ($fromAgent) {
            $recipient = optional($ticket->user)->email;
            if ($recipient) {
                Mail::to($recipient)->queue(new TicketRepliedMail($ticket, $message, forCustomer: true));
            }

            return;
        }

        $admins = (array) config('customer-support.mail.admin_recipients', []);
        if (optional($ticket->assignee)->email) {
            $admins = array_unique(array_merge($admins, [$ticket->assignee->email]));
        }

        if (! empty($admins)) {
            Mail::to(array_values($admins))->queue(new TicketRepliedMail($ticket, $message, forCustomer: false));
        }
    }
}
