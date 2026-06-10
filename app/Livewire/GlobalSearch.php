<?php

namespace App\Livewire;

use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';
    
    public function getFeaturesProperty()
    {
        return [
            ['title' => 'Dashboard Utama', 'url' => route('dashboard'), 'icon' => 'dashboard', 'keywords' => 'home beranda awal dashboard'],
            ['title' => 'Manpower Planning (MPP)', 'url' => route('mpp.index'), 'icon' => 'group_add', 'keywords' => 'mpp manpower planning daftar list index'],
            ['title' => 'Buat Plan Baru (MPP)', 'url' => route('mpp.create'), 'icon' => 'add_circle', 'keywords' => 'buat bikin tambah plan mpp create new mpp/create'],
            ['title' => 'Recruitment Requests (RR)', 'url' => route('rr.index'), 'icon' => 'description', 'keywords' => 'rr recruitment request daftar list index'],
            ['title' => 'Buat RR Baru', 'url' => route('rr.create'), 'icon' => 'add_circle', 'keywords' => 'buat bikin tambah rr create new recruitment request rr/create'],
            ['title' => 'Pipeline Stages', 'url' => route('ats.dashboard'), 'icon' => 'account_tree', 'keywords' => 'ats applicant tracking system pipeline board kanban'],
            ['title' => 'Daftar Kandidat (Candidates List )', 'url' => route('ats.candidates'), 'icon' => 'group', 'keywords' => 'semua kandidat all candidates daftar list index pelamar'],
            ['title' => 'Buat / Config Stage (ATS)', 'url' => route('ats.stages'), 'icon' => 'settings', 'keywords' => 'buat setting config stage tahapan ats ats/buat stage setting/...'],
            ['title' => 'Buat / Tambah Kandidat (ATS)', 'url' => route('ats.candidate.manual'), 'icon' => 'person_add', 'keywords' => 'buat tambah kandidat manual ats pelamar ats/buat kandidat'],
            ['title' => 'Kandidat Blacklist', 'url' => route('ats.blacklist'), 'icon' => 'block', 'keywords' => 'blacklist daftar hitam kandidat diblokir'],
            ['title' => 'Notifikasi', 'url' => route('hr.notifications'), 'icon' => 'notifications', 'keywords' => 'notifikasi in-app pemberitahuan pesan baru'],
            ['title' => 'Settings (Pengaturan)', 'url' => route('hr.settings'), 'icon' => 'settings', 'keywords' => 'settings pengaturan logout keluar'],
            ['title' => 'Profil Saya', 'url' => route('hr.profile'), 'icon' => 'person', 'keywords' => 'profil saya akun hr detail'],
            ['title' => 'Edit Profil', 'url' => route('hr.profile.edit'), 'icon' => 'edit', 'keywords' => 'edit ubah profil ganti data diri setting'],
            ['title' => 'Ubah Password', 'url' => route('hr.profile.password'), 'icon' => 'lock', 'keywords' => 'ubah ganti password kata sandi keamanan setting'],
        ];
    }

    public function getSearchResultsProperty()
    {
        if (empty(trim($this->query))) {
            return [];
        }

        $search = strtolower(trim($this->query));
        
        // 1. Filter static features
        $results = collect($this->features)->filter(function ($feature) use ($search) {
            return str_contains(strtolower($feature['title']), $search) || 
                   str_contains(strtolower($feature['keywords']), $search);
        })->values()->toArray();

        // 2. Search Candidates in DB
        $candidates = \App\Models\Candidate::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->take(3)
            ->get();
            
        foreach ($candidates as $candidate) {
            $results[] = [
                'title' => "Kandidat: {$candidate->name}",
                'url' => route('ats.candidate.detail', ['candidateId' => $candidate->id]),
                'icon' => 'person',
                'keywords' => 'kandidat pelamar'
            ];
        }

        // 3. Search Blacklisted Candidates in DB
        $blacklists = \App\Models\Blacklist::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->take(3)
            ->get();

        foreach ($blacklists as $blacklist) {
            $results[] = [
                'title' => "Blacklist: {$blacklist->name} ({$blacklist->reason})",
                'url' => route('ats.blacklist') . '?search=' . urlencode($blacklist->name),
                'icon' => 'block',
                'keywords' => 'blacklist'
            ];
        }

        // 4. Search MPP in DB
        $mpps = \App\Models\Mpp::where('plan_name', 'like', "%{$search}%")
            ->orWhere('job_title', 'like', "%{$search}%")
            ->take(3)
            ->get();

        foreach ($mpps as $mpp) {
            $results[] = [
                'title' => "MPP Plan: {$mpp->plan_name}",
                'url' => route('mpp.show', ['id' => $mpp->id]),
                'icon' => 'group_add',
                'keywords' => 'mpp planning'
            ];
        }

        // 5. Search RR in DB
        $rrs = \App\Models\Rr::where('job_title', 'like', "%{$search}%")
            ->orWhere('department', 'like', "%{$search}%")
            ->take(3)
            ->get();

        foreach ($rrs as $rr) {
            $results[] = [
                'title' => "RR: {$rr->job_title}",
                'url' => route('rr.show', ['id' => $rr->id]),
                'icon' => 'description',
                'keywords' => 'rr request'
            ];
        }

        // Return top 8 results
        return array_slice($results, 0, 8);
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
