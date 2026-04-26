<x-mail::message>
@if ($forCustomer)
# New reply on your ticket

We've added a new reply to your ticket **{{ $ticket->ticket_number }} — {{ $ticket->subject }}**.

> {{ $message->body }}

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
Open ticket
</x-mail::button>

You can reply by visiting the ticket page above.
@else
# Customer replied to ticket {{ $ticket->ticket_number }}

**{{ $ticket->user?->name ?? $ticket->user?->email ?? 'Customer' }}** sent a new reply on **{{ $ticket->subject }}**.

> {{ $message->body }}

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
View ticket
</x-mail::button>
@endif
</x-mail::message>
