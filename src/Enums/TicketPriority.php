<?php

namespace A2ZWeb\CustomerSupport\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Normal => 'Normal',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'zinc',
            self::Normal => 'blue',
            self::High => 'amber',
            self::Urgent => 'red',
        };
    }

    public function slaHours(): int
    {
        $hours = config('customer-support.sla_hours', []);

        return (int) ($hours[$this->value] ?? match ($this) {
            self::Urgent => 4,
            self::High => 12,
            self::Normal => 48,
            self::Low => 120,
        });
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
