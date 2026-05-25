<?php

use A2ZWeb\CustomerSupport\Actions\CreateTicket;
use A2ZWeb\CustomerSupport\DataTransferObjects\TicketData;
use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Livewire\TicketShow;
use A2ZWeb\CustomerSupport\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    // The shipped views use Flux (only available in the host app). Prepend a
    // Flux-free stub so the component can render inside the package testbench.
    $factory = app('view');
    $hints = $factory->getFinder()->getHints()['customer-support'] ?? [];
    $factory->replaceNamespace('customer-support', array_merge([__DIR__.'/../stubs/views'], $hints));
});

function ticketOwnedBy(User $user)
{
    Mail::fake();

    return app(CreateTicket::class)->execute(new TicketData(
        userId: $user->id,
        subject: 'Attachment handling',
        description: 'Testing the reply attachment flow.',
        category: TicketCategory::Bug,
    ));
}

it('appends a single uploaded file to attachments and clears the staging slot', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('attachment', UploadedFile::fake()->create('one.png', 50, 'image/png'))
        ->assertSet('attachment', null)
        ->assertCount('attachments', 1);
});

it('appends files one at a time instead of replacing them', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('attachment', UploadedFile::fake()->create('one.png', 50, 'image/png'))
        ->set('attachment', UploadedFile::fake()->create('two.png', 50, 'image/png'))
        ->assertCount('attachments', 2);
});

it('rejects a file that exceeds the configured size limit', function () {
    Storage::fake('public');
    config()->set('customer-support.attachments.max_size_kb', 100);
    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('attachment', UploadedFile::fake()->create('big.png', 500, 'image/png'))
        ->assertHasErrors('attachment')
        ->assertCount('attachments', 0);
});

it('removes an attachment by index and reindexes the list', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('attachment', UploadedFile::fake()->create('one.png', 50, 'image/png'))
        ->set('attachment', UploadedFile::fake()->create('two.png', 50, 'image/png'))
        ->call('removeAttachment', 0)
        ->assertCount('attachments', 1)
        ->tap(fn ($c) => expect($c->get('attachments')[0]->getClientOriginalName())->toBe('two.png'));
});

it('attaches staged files as media on the new message and resets state', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('reply', 'Here is the file you asked for.')
        ->set('attachment', UploadedFile::fake()->create('proof.png', 50, 'image/png'))
        ->call('postReply')
        ->assertHasNoErrors()
        ->assertSet('attachments', [])
        ->assertSet('reply', '');

    $message = $ticket->messages()->latest('id')->first();

    expect($message->getMedia('attachments')->count())->toBe(1)
        ->and($message->getMedia('attachments')->first()->file_name)->toBe('proof.png');
});

it('attaches a staged file regardless of the livewire temporary upload disk', function (string $tempDisk) {
    // On prod the Livewire temporary-upload disk is S3, so the staged file is a
    // remote TemporaryUploadedFile whose local path does not exist — the old
    // path-based addMedia() blew up with FileDoesNotExist. We now read the bytes
    // via the file's own disk, so attaching must work whatever disk holds the
    // temp file. (Storage::fake is always local-backed, so it can't reproduce
    // S3's missing-local-path quirk exactly; this guards the disk-agnostic
    // contract and content integrity across disks.)
    Storage::fake('public');
    Storage::fake($tempDisk);
    config()->set('livewire.temporary_file_upload.disk', $tempDisk);

    $user = User::factory()->create();
    $ticket = ticketOwnedBy($user);

    actingAs($user);

    Livewire::test(TicketShow::class, ['ticket' => $ticket])
        ->set('reply', 'Here is the file you asked for.')
        ->set('attachment', UploadedFile::fake()->create('proof.png', 50, 'image/png'))
        ->call('postReply')
        ->assertHasNoErrors();

    $media = $ticket->messages()->latest('id')->first()->getMedia('attachments')->first();

    expect($media)->not->toBeNull()
        ->and($media->file_name)->toBe('proof.png')
        ->and($media->disk)->toBe('public')
        ->and(Storage::disk('public')->exists($media->getPathRelativeToRoot()))->toBeTrue();
})->with([
    'local temp disk (dev)' => 'local',
    'dedicated s3 temp disk (prod)' => 's3',
]);

it('keeps the multiple attribute off the shipped attachment inputs', function () {
    $views = [
        __DIR__.'/../../resources/views/livewire/ticket-show.blade.php',
        __DIR__.'/../../resources/views/livewire/ticket-create.blade.php',
    ];

    foreach ($views as $view) {
        $markup = file_get_contents($view);

        expect($markup)->toContain('wire:model="attachment"')
            ->and($markup)->not->toMatch('/type="file"[^>]*\bmultiple\b/');
    }
});
