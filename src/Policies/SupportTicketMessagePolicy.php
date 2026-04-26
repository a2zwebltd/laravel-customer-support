<?php

namespace A2ZWeb\CustomerSupport\Policies;

use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class SupportTicketMessagePolicy
{
    public function view(Authenticatable $user, SupportTicketMessage $message): bool
    {
        if ($message->is_internal_note) {
            return $this->isAgent($user);
        }

        if ($this->isAgent($user)) {
            return true;
        }

        return (int) $message->ticket->user_id === (int) $user->getAuthIdentifier();
    }

    public function delete(Authenticatable $user, SupportTicketMessage $message): bool
    {
        return $this->isAgent($user);
    }

    private function isAgent(Authenticatable $user): bool
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');

        return Gate::forUser($user)->allows($gate);
    }
}
