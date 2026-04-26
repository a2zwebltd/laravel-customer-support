<?php

namespace A2ZWeb\CustomerSupport\DataTransferObjects;

use Illuminate\Http\UploadedFile;

final readonly class MessageData
{
    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function __construct(
        public int $ticketId,
        public int $userId,
        public string $body,
        public bool $isInternalNote = false,
        public array $attachments = [],
    ) {}
}
