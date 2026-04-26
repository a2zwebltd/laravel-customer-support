<?php

namespace A2ZWeb\CustomerSupport\DataTransferObjects;

use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use Illuminate\Http\UploadedFile;

final readonly class TicketData
{
    /**
     * @param  array<int, UploadedFile>  $attachments
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public int $userId,
        public string $subject,
        public string $description,
        public TicketCategory $category,
        public TicketPriority $priority = TicketPriority::Normal,
        public array $attachments = [],
        public ?array $metadata = null,
    ) {}
}
