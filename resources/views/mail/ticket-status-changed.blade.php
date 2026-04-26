<x-mail::message>
# Status update on your ticket

Your ticket **{{ $ticket->ticket_number }} — {{ $ticket->subject }}** has been updated.

**Status:** {{ $previous->label() }} → **{{ $current->label() }}**

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
View ticket
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
