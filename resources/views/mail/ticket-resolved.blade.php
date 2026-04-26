<x-mail::message>
# Your ticket has been resolved

We've marked **{{ $ticket->ticket_number }} — {{ $ticket->subject }}** as resolved. We hope we were able to help.

If you still need anything — just reply on the ticket page and we'll re-open it for you.

<x-mail::button :url="url('/support/'.$ticket->ticket_number)">
Review the ticket
</x-mail::button>

Thanks for working with us,<br>
{{ config('app.name') }}
</x-mail::message>
