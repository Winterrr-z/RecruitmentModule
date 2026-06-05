<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Mpp\MppIndex;
use App\Livewire\Mpp\MppDetail;
use App\Livewire\Rr\RRIndex;
use App\Livewire\Rr\RRForm;
use App\Livewire\Rr\RRDetail;
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
// Applicant & HR Auth (Registrasi & Login)
// ---------------------------------------------------------------------------
Route::get('/register', App\Livewire\Cw\RegisterApplicant::class)->name('candidate.register');
Route::get('/login', App\Livewire\Cw\LoginApplicant::class)->name('candidate.login');
Route::get('/hr/login', App\Livewire\Hr\LoginHr::class)->name('hr.login');
Route::get('/hr/forgot-password', App\Livewire\Hr\ForgotPasswordHr::class)->name('hr.password.request');
Route::get('/hr/reset-password/{token}', App\Livewire\Hr\ResetPasswordHr::class)->name('password.reset');
Route::get('/login-redirect', fn() => redirect()->route('candidate.login'))->name('login');
Route::match(['get', 'post'], '/logout', function () {
    $role = Auth::user()?->role; // simpan role sebelum logout
    Auth::logout();
    
    if ($role === 'hr') {
        return redirect()->route('hr.login');
    }
    return redirect()->route('careers');
})->name('logout');

// ---------------------------------------------------------------------------
// Candidate area (pelamar yang sudah login)
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/candidate/dashboard', App\Livewire\Cw\CandidateDashboard::class)->name('candidate.dashboard');
    Route::get('/dashboard', App\Livewire\DashboardIndex::class)->name('dashboard');
    Route::get('/profile', App\Livewire\Hr\ProfileHr::class)->name('hr.profile');
    Route::get('/profile/edit', App\Livewire\Hr\EditProfileHr::class)->name('hr.profile.edit');
    Route::get('/profile/change-password', App\Livewire\Hr\ChangePasswordHr::class)->name('hr.profile.password');
    Route::get('/settings', App\Livewire\Hr\SettingsHr::class)->name('hr.settings');
    Route::get('/notifications', App\Livewire\Hr\NotificationsHr::class)->name('hr.notifications');
    Route::get('/jobs', App\Livewire\Cw\CandidateJobList::class)->name('candidate.jobs');
    Route::get('/jobs/{id}', App\Livewire\Cw\CandidateJobDetail::class)->name('candidate.jobs.show');
    Route::get('/jobs/{id}/apply', App\Livewire\Cw\CandidateJobDetail::class)->name('candidate.apply');
});

// ---------------------------------------------------------------------------
// ATS area (Applicant Tracking System)
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->prefix('ats')->group(function () {
    Route::get('/', App\Livewire\Ats\AtsPipeline::class)->name('ats.dashboard');
    Route::get('/candidates', App\Livewire\Ats\AtsAllCandidates::class)->name('ats.candidates');
    Route::get('/stages', App\Livewire\Ats\AtsStageConfig::class)->name('ats.stages');
    Route::get('/blacklist', App\Livewire\Ats\AtsBlacklist::class)->name('ats.blacklist');
    Route::get('/manual/{lowonganId?}', App\Livewire\Ats\AtsManualCandidate::class)->name('ats.candidate.manual');
    Route::get('/candidate/{candidateId}', App\Livewire\Ats\AtsCandidateDetail::class)->name('ats.candidate.detail');
    Route::get('/candidate/{candidateId}/schedule/{stageId}', App\Livewire\Ats\AtsScheduleForm::class)->name('ats.candidate.schedule');
    Route::get('/candidate/{candidateId}/scorecard/{stageId}', App\Livewire\Ats\AtsScorecardForm::class)->name('ats.candidate.scorecard');
});

// Offering (HR)
Route::get('/ats/offering/{candidateId}', App\Livewire\OfferingSend::class)->name('ats.offering.send')->middleware('auth');

// Offering Response (Publik)
Route::get('/offering/{token}', App\Livewire\OfferingResponse::class)->name('offering.response');
Route::post('/offering/{token}/respond', [App\Livewire\OfferingResponse::class, 'respond'])->name('offering.respond');


// ---------------------------------------------------------------------------
// Dev helpers — Login/Logout cepat untuk testing layout applicant
// ---------------------------------------------------------------------------
if (app()->environment(['local', 'testing'])) {
    Route::get('/dev/login', function () {
        $user = User::first() ?? User::factory()->create([
            'name'  => 'Admin Utama',
            'email' => 'raja.wijayaaa@gmail.com',
            'job_title' => 'HR Manager',
            'departemen' => 'Human Capital and General Affairs',
            'role'  => 'hr',
        ]);
        Auth::login($user);
        return redirect()->route('dashboard');
    })->name('dev.login');

    Route::get('/dev/login-applicant', function () {
        $user = User::where('role', 'applicant')->first() ?? User::factory()->create([
            'name'  => 'Pelamar Demo',
            'email' => 'pelamar@email.com',
            'role'  => 'applicant',
        ]);
        Auth::login($user);
        return redirect()->route('candidate.dashboard');
    })->name('dev.login.applicant');


    Route::get('/dev/logout', function () {
        Auth::logout();
        return redirect()->route('careers');
    })->name('dev.logout');

    Route::get('/dev/simulate/hired', function () {
        if (!Auth::check() || Auth::user()->role !== 'applicant') return "Login sebagai pelamar dulu!";
        $candidate = \App\Models\Candidate::where('user_id', Auth::id())->first();
        if (!$candidate) return "Pelamar ini belum melamar lowongan apa pun.";
        
        $candidate->update([
            'status' => 'Hired',
            'offering_token' => null,
            'offering_token_expires_at' => null,
        ]);
        return redirect()->route('candidate.dashboard')->with('success', 'Simulasi: Status diubah menjadi Hired.');
    });
    Route::get('/dev/simulate/offering', function () {
        if (!Auth::check() || Auth::user()->role !== 'applicant') return "Login sebagai pelamar dulu!";
        $candidate = \App\Models\Candidate::where('user_id', Auth::id())->where('status', '!=', 'Hired')->first();
        if (!$candidate) return "Pelamar tidak memiliki lamaran aktif.";
        
        $finalStage = \App\Models\Stage::where('nama', 'Final')->first();
        $candidate->update([
            'status' => 'In Progress',
            'current_stage_id' => $finalStage ? $finalStage->id : $candidate->current_stage_id,
            'offering_token' => \Illuminate\Support\Str::random(40),
            'offering_token_expires_at' => now()->addDays(3),
        ]);
        return redirect()->route('candidate.dashboard')->with('success', 'Simulasi: Penawaran (Offering) telah diberikan.');
    });
}

// ---------------------------------------------------------------------------
// Root — redirect ke dashboard untuk HR, careers untuk yang lain
// ---------------------------------------------------------------------------
Route::get('/', function () {
    if (Auth::check() && Auth::user()->role === 'hr') {
        return redirect()->route('dashboard');
    }
    return redirect()->route('careers');
});
