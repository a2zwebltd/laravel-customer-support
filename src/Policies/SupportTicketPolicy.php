<?php

namespace A2ZWeb\CustomerSupport\Policies;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class SupportTicketPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, SupportTicket $ticket): bool
    {
        if ($this->isAgent($user)) {
            return true;
        }

        return (int) $ticket->user_id === (int) $user->getAuthIdentifier();
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function reply(Authenticatable $user, SupportTicket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function changeStatus(Authenticatable $user, SupportTicket $ticket): bool
    {
        return $this->isAgent($user);
    }

    public function assign(Authenticatable $user, SupportTicket $ticket): bool
    {
        return $this->isAgent($user);
    }

    public function addInternalNote(Authenticatable $user, SupportTicket $ticket): bool
    {
        return $this->isAgent($user);
    }

    public function delete(Authenticatable $user, SupportTicket $ticket): bool
    {
        return $this->isAgent($user);
    }

    private function isAgent(Authenticatable $user): bool
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');

        return Gate::forUser($user)->allows($gate);
    }
}
