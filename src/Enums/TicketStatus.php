<?php

namespace A2ZWeb\CustomerSupport\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case AwaitingCustomer = 'awaiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::AwaitingCustomer => 'Awaiting Customer',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::Pending => 'zinc',
            self::InProgress => 'amber',
            self::AwaitingCustomer => 'purple',
            self::Resolved => 'green',
            self::Closed => 'zinc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'envelope-open',
            self::Pending => 'clock',
            self::InProgress => 'arrow-path',
            self::AwaitingCustomer => 'chat-bubble-left-right',
            self::Resolved => 'check-circle',
            self::Closed => 'archive-box',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [
            self::Open,
            self::Pending,
            self::InProgress,
            self::AwaitingCustomer,
        ], true);
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Resolved, self::Closed], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
