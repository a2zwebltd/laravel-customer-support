<x-mail::message>
@if ($forAgent)
# New support ticket — {{ $ticket->ticket_number }}

A new support ticket was just submitted by **{{ $ticket->user?->name ?? $ticket->user?->email ?? 'a customer' }}**.

**Subject:** {{ $ticket->subject }}
**Category:** {{ $ticket->category->label() }}
**Priority:** {{ $ticket->priority->label() }}
**SLA due:** {{ optional($ticket->due_at)->format('Y-m-d H:i') ?? '—' }}

> {{ \Illuminate\Support\Str::limit($ticket->description, 600) }}

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
View ticket
</x-mail::button>

@else
# Thanks — we got your ticket

Hi {{ $ticket->user?->name ?? '' }},

We've received your support ticket. A member of our team will get back to you as soon as possible.

**Ticket number:** {{ $ticket->ticket_number }}
**Subject:** {{ $ticket->subject }}
**Priority:** {{ $ticket->priority->label() }}

Here's a copy of what you sent us:

> {{ \Illuminate\Support\Str::limit($ticket->description, 600) }}

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
View ticket
</x-mail::button>

Thanks for reaching out,<br>
{{ config('app.name') }}
@endif
</x-mail::message>
