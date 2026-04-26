<?php

namespace A2ZWeb\CustomerSupport\Concerns;

use A2ZWeb\CustomerSupport\Models\SupportTicket;
use A2ZWeb\CustomerSupport\Models\SupportTicketMessage;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSupportTickets
{
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    public function assignedSupportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    public function supportTicketMessages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'user_id');
    }
}
