<?php

use A2ZWeb\CustomerSupport\Livewire\Admin\TicketsDashboard;
use A2ZWeb\CustomerSupport\Livewire\TicketCreate;
use A2ZWeb\CustomerSupport\Livewire\TicketShow;
use A2ZWeb\CustomerSupport\Livewire\TicketsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', TicketsIndex::class)->name('index');
Route::get('/new', TicketCreate::class)->name('create');
Route::get('/admin', TicketsDashboard::class)->name('admin.dashboard');
Route::get('/{ticket}', TicketShow::class)->name('show');
