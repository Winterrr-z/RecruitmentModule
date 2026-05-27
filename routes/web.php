<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Mpp\MppIndex;
use App\Livewire\Mpp\MppDetail;
use App\Livewire\Rr\RRIndex;
use App\Livewire\Rr\RRForm;
use App\Livewire\Cw\CareerJobList;
use App\Models\User;

// ---------------------------------------------------------------------------
// Rute Modul HR (MPP, Recruitment Request)
// ---------------------------------------------------------------------------
Route::middleware(['web'])->group(function () {
    Route::get('/mpp', App\Livewire\Mpp\MppIndex::class)->name('mpp.index');
    Route::get('/mpp/create', App\Livewire\Mpp\MppForm::class)->name('mpp.create');
    Route::get('/mpp/{id}/edit', App\Livewire\Mpp\MppForm::class)->name('mpp.edit');
    Route::get('/mpp/{id}', App\Livewire\Mpp\MppDetail::class)->name('mpp.show');

    Route::get('/recruitment-requests', App\Livewire\Rr\RRIndex::class)->name('rr.index');
    Route::get('/recruitment-requests/create/{mppId?}', App\Livewire\Rr\RRForm::class)->name('rr.create');
    Route::get('/recruitment-requests/{id}/edit', App\Livewire\Rr\RRForm::class)->name('rr.edit');
    Route::get('/recruitment-requests/{id}', App\Livewire\Rr\RRDetail::class)->name('rr.show');
});

// ---------------------------------------------------------------------------
// Rute Publik Careers
// ---------------------------------------------------------------------------
Route::get('/careers', App\Livewire\Cw\PublicJobList::class)->name('careers');
Route::get('/blacklist-info', fn() => view('blacklist-info'))->name('blacklist.info');

// ---------------------------------------------------------------------------
// Applicant Auth (Registrasi & Login)
// ---------------------------------------------------------------------------
Route::get('/register', App\Livewire\Cw\RegisterApplicant::class)->name('candidate.register');
Route::get('/login', App\Livewire\Cw\LoginApplicant::class)->name('candidate.login');
Route::get('/login-redirect', fn() => redirect()->route('candidate.login'))->name('login');
Route::post('/logout', function () { Auth::logout(); return redirect('/'); })->name('candidate.logout');

// ---------------------------------------------------------------------------
// Candidate area (pelamar yang sudah login)
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', App\Livewire\Cw\CandidateDashboard::class)->name('candidate.dashboard');
    Route::get('/jobs', App\Livewire\Cw\CandidateJobList::class)->name('candidate.jobs');
    Route::get('/jobs/{id}', App\Livewire\Cw\CandidateJobDetail::class)->name('candidate.jobs.show');
    Route::get('/jobs/{id}/apply', App\Livewire\Cw\CandidateJobDetail::class)->name('candidate.apply');
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
