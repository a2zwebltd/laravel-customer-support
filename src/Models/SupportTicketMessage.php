<?php

namespace A2ZWeb\CustomerSupport\Models;

use A2ZWeb\CustomerSupport\Database\Factories\SupportTicketMessageFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $body
 * @property bool $is_internal_note
 * @property Carbon|null $created_at
 */
class SupportTicketMessage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('customer-support.tables.prefix', '').config('customer-support.tables.messages', 'support_ticket_messages');
    }

    protected function casts(): array
    {
        return [
            'is_internal_note' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $disk = config('customer-support.attachments.disk', 'public');
        $this->addMediaCollection(config('customer-support.attachments.collection', 'attachments'))
            ->useDisk($disk);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        $userModel = config('customer-support.user_model')
            ?? config('auth.providers.'.config('auth.guards.web.provider', 'users').'.model', User::class);

        return $this->belongsTo($userModel, 'user_id');
    }

    public function isFromAgent(): bool
    {
        $gate = config('customer-support.admin_gate', 'manage-support-tickets');
        $user = $this->user;
        if (! $user) {
            return false;
        }

        return Gate::forUser($user)->allows($gate);
    }

    protected static function newFactory(): Factory
    {
        return SupportTicketMessageFactory::new();
    }
}
