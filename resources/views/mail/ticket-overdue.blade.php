<x-mail::message>
# Ticket overdue — needs attention

Ticket **{{ $ticket->ticket_number }} — {{ $ticket->subject }}** is past its SLA and is still open.

- **Status:** {{ $ticket->status->label() }}
- **Priority:** {{ $ticket->priority->label() }}
- **Due:** {{ $ticket->due_at?->toDayDateTimeString() }} ({{ $ticket->due_at?->diffForHumans() }})

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
View ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
