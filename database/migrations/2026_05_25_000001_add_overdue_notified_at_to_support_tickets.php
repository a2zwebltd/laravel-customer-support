<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->timestamp('overdue_notified_at')->nullable()->after('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table): void {
            $table->dropColumn('overdue_notified_at');
        });
    }

    private function table(): string
    {
        return config('customer-support.tables.prefix', '').config('customer-support.tables.tickets', 'support_tickets');
    }
};
