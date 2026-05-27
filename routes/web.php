<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\MppIndex;
use App\Livewire\MppDetail;
use App\Livewire\RRIndex;
use App\Livewire\RRForm;
use App\Livewire\CareerJobList;
use App\Models\User;

// ---------------------------------------------------------------------------
// Rute Modul HR (MPP, Recruitment Request)
// ---------------------------------------------------------------------------
Route::middleware(['web'])->group(function () {
    Route::get('/mpp', App\Livewire\MppIndex::class)->name('mpp.index');
    Route::get('/mpp/create', App\Livewire\MppForm::class)->name('mpp.create');
    Route::get('/mpp/{id}/edit', App\Livewire\MppForm::class)->name('mpp.edit');
    Route::get('/mpp/{id}', App\Livewire\MppDetail::class)->name('mpp.show');

    Route::get('/recruitment-requests', App\Livewire\RRIndex::class)->name('rr.index');
    Route::get('/recruitment-requests/create/{mppId?}', App\Livewire\RRForm::class)->name('rr.create');
    Route::get('/recruitment-requests/{id}', App\Livewire\RRDetail::class)->name('rr.show');
});

// ---------------------------------------------------------------------------
// Rute Publik Careers
// ---------------------------------------------------------------------------
Route::get('/careers', App\Livewire\PublicJobList::class)->name('careers');
Route::get('/blacklist-info', fn() => view('blacklist-info'))->name('blacklist.info');

// ---------------------------------------------------------------------------
// Applicant Auth (Registrasi & Login)
// ---------------------------------------------------------------------------
Route::get('/register', App\Livewire\RegisterApplicant::class)->name('candidate.register');
Route::get('/login', App\Livewire\LoginApplicant::class)->name('candidate.login');
Route::get('/login-redirect', fn() => redirect()->route('candidate.login'))->name('login');
Route::post('/logout', function () { Auth::logout(); return redirect('/'); })->name('candidate.logout');

// ---------------------------------------------------------------------------
// Candidate area (pelamar yang sudah login)
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', App\Livewire\CandidateDashboard::class)->name('candidate.dashboard');
    Route::get('/jobs', App\Livewire\CandidateJobList::class)->name('candidate.jobs');
    Route::get('/jobs/{id}', App\Livewire\CandidateJobDetail::class)->name('candidate.jobs.show');
    Route::get('/jobs/{id}/apply', App\Livewire\CandidateJobDetail::class)->name('candidate.apply');
});

// ---------------------------------------------------------------------------
// Dev helpers — Login/Logout cepat untuk testing layout applicant
// ---------------------------------------------------------------------------
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/login', function () {
        $user = User::first() ?? User::factory()->create([
            'name'  => 'Admin Utama',
            'email' => 'admin@humanfirst.com',
            'role'  => 'hr',
        ]);
        Auth::login($user);
        return redirect()->route('careers');
    })->name('dev.login');

    Route::get('/dev/logout', function () {
        Auth::logout();
        return redirect()->route('careers');
    })->name('dev.logout');
}

// ---------------------------------------------------------------------------
// Root — redirect ke halaman careers
// ---------------------------------------------------------------------------
Route::get('/', function () {
    return redirect()->route('careers');
});
