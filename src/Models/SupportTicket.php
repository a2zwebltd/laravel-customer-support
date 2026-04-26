<?php

namespace A2ZWeb\CustomerSupport\Models;

use A2ZWeb\CustomerSupport\Database\Factories\SupportTicketFactory;
use A2ZWeb\CustomerSupport\Enums\TicketCategory;
use A2ZWeb\CustomerSupport\Enums\TicketPriority;
use A2ZWeb\CustomerSupport\Enums\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $ticket_number
 * @property int $user_id
 * @property int|null $assigned_to
 * @property string $subject
 * @property string $description
 * @property TicketCategory $category
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property Carbon|null $first_response_at
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $due_at
 * @property Carbon|null $last_activity_at
 * @property array<string, mixed>|null $metadata
 */
class SupportTicket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('customer-support.tables.prefix', '').config('customer-support.tables.tickets', 'support_tickets');
    }

    protected function casts(): array
    {
        return [
            'category' => TicketCategory::class,
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'due_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $ticket): void {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
            if (empty($ticket->status)) {
                $ticket->status = TicketStatus::Open;
            }
            if (empty($ticket->priority)) {
                $ticket->priority = TicketPriority::Normal;
            }
            if (empty($ticket->last_activity_at)) {
                $ticket->last_activity_at = now();
            }
            if (empty($ticket->due_at)) {
                $priority = $ticket->priority instanceof TicketPriority
                    ? $ticket->priority
                    : TicketPriority::tryFrom((string) $ticket->priority) ?? TicketPriority::Normal;
                $ticket->due_at = now()->addHours($priority->slaHours());
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $disk = config('customer-support.attachments.disk', 'public');
        $this->addMediaCollection(config('customer-support.attachments.collection', 'attachments'))
            ->useDisk($disk);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo($this->resolveUserModel(), 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo($this->resolveUserModel(), 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function publicMessages(): HasMany
    {
        return $this->messages()->where('is_internal_note', false);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TicketStatus::Open->value,
            TicketStatus::Pending->value,
            TicketStatus::InProgress->value,
            TicketStatus::AwaitingCustomer->value,
        ]);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ]);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isOverdue(): bool
    {
        return $this->status->isOpen()
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }

    protected static function newFactory(): Factory
    {
        return SupportTicketFactory::new();
    }

    public static function generateTicketNumber(): string
    {
        $prefix = config('customer-support.ticket_number.prefix', 'CS-');
        $pad = (int) config('customer-support.ticket_number.pad', 6);

        $latest = static::query()->orderByDesc('id')->value('id') ?? 0;
        $next = $latest + 1;

        return $prefix.str_pad((string) $next, $pad, '0', STR_PAD_LEFT);
    }

    private function resolveUserModel(): string
    {
        return config('customer-support.user_model')
            ?? config('auth.providers.'.config('auth.guards.web.provider', 'users').'.model', User::class);
    }
}
