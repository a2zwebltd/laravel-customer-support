{{-- Minimal Flux-free stub so TicketShow can render inside the package testbench. --}}
<div>
    <ul>
        @foreach ($attachments as $i => $file)
            <li>{{ $file->getClientOriginalName() }}</li>
        @endforeach
    </ul>
</div>
