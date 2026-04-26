<?php

namespace A2ZWeb\CustomerSupport\Enums;

/**
 * Default categories. The host application can override the user-facing label
 * and icon via config('customer-support.categories'). The enum cases below
 * mirror the default keys.
 */
enum TicketCategory: string
{
    case Bug = 'bug';
    case Question = 'question';
    case Billing = 'billing';
    case FeatureRequest = 'feature_request';
    case Account = 'account';
    case Other = 'other';

    public function label(): string
    {
        return (string) (config("customer-support.categories.{$this->value}.label") ?? match ($this) {
            self::Bug => 'Bug Report',
            self::Question => 'Question',
            self::Billing => 'Billing & Payments',
            self::FeatureRequest => 'Feature Request',
            self::Account => 'Account & Access',
            self::Other => 'Other',
        });
    }

    public function icon(): string
    {
        return (string) (config("customer-support.categories.{$this->value}.icon") ?? match ($this) {
            self::Bug => 'bug-ant',
            self::Question => 'question-mark-circle',
            self::Billing => 'credit-card',
            self::FeatureRequest => 'sparkles',
            self::Account => 'user-circle',
            self::Other => 'ellipsis-horizontal',
        });
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function configured(): array
    {
        $configured = config('customer-support.categories', []);

        if (empty($configured)) {
            $configured = [];
            foreach (self::cases() as $case) {
                $configured[$case->value] = ['label' => $case->label(), 'icon' => $case->icon()];
            }
        }

        return $configured;
    }
}
