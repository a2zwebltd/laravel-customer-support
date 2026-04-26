<?php

namespace A2ZWeb\CustomerSupport\Actions;

use A2ZWeb\CustomerSupport\DataTransferObjects\MessageData;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;

class AddInternalNote
{
    public function __construct(private readonly ReplyToTicket $reply) {}

    public function execute(int $ticketId, int $userId, string $body, array $attachments = []): SupportTicketMessage
    {
        return $this->reply->execute(new MessageData(
            ticketId: $ticketId,
            userId: $userId,
            body: $body,
            isInternalNote: true,
            attachments: $attachments,
        ));
    }
}
