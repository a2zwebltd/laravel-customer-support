<?php

namespace A2ZWeb\CustomerSupport\DataTransferObjects;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Carbon\CarbonInterface;

final readonly class SlaTracking
{
    public function __construct(
        public ?CarbonInterface $dueAt,
        public bool $breached,
        public ?int $minutesRemaining,
        public ?int $minutesOverdue,
    ) {}

    public static function forTicket(SupportTicket $ticket): self
    {
        if ($ticket->due_at === null || ! $ticket->status->isOpen()) {
            return new self(null, false, null, null);
        }

        $now = now();
        $breached = $ticket->due_at->isPast();
        $diffMinutes = (int) abs($now->diffInMinutes($ticket->due_at, false));

        return new self(
            dueAt: $ticket->due_at,
            breached: $breached,
            minutesRemaining: $breached ? null : $diffMinutes,
            minutesOverdue: $breached ? $diffMinutes : null,
        );
    }

    public function humanLabel(): string
    {
        if ($this->dueAt === null) {
            return 'No SLA';
        }

        if ($this->breached) {
            return 'Overdue by '.$this->humanizeMinutes($this->minutesOverdue ?? 0);
        }

        return $this->humanizeMinutes($this->minutesRemaining ?? 0).' remaining';
    }

    private function humanizeMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.'m';
        }
        if ($minutes < 60 * 24) {
            return floor($minutes / 60).'h '.($minutes % 60).'m';
        }

        return floor($minutes / 1440).'d '.floor(($minutes % 1440) / 60).'h';
    }
}
