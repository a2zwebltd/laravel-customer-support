<?php

namespace A2ZWeb\CustomerSupport\Tests;

use A2ZWeb\CustomerSupport\Concerns\HasSupportTickets;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, HasSupportTickets, InteractsWithMedia, Notifiable;

    protected $guarded = [];

    protected $hidden = ['password'];

    public function isAdmin(): bool
    {
        return (bool) ($this->is_admin ?? false);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
