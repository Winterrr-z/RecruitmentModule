<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\MppIndex;
use App\Livewire\MppDetail;

Route::get('/mpp', MppIndex::class)->name('mpp.index');
Route::get('/mpp/{mppId}', MppDetail::class)->name('mpp.show');
Route::get('/mpp/{mppId}/edit', function ($mppId) {
    return redirect()->route('mpp.index', ['edit_id' => $mppId]);
})->name('mpp.edit');

Route::get('/rr/create', function () {
    return 'Create Recruitment Request Page Placeholder';
})->name('rr.create');

Route::get('/', function () {
    return view('welcome');
});
