<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained($this->ticketsTable())->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal_note')->default(false)->index();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    private function table(): string
    {
        return config('customer-support.tables.prefix', '').config('customer-support.tables.messages', 'support_ticket_messages');
    }

    private function ticketsTable(): string
    {
        return config('customer-support.tables.prefix', '').config('customer-support.tables.tickets', 'support_tickets');
    }
};
