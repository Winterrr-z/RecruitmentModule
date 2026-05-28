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
            ['title' => 'ATS Pipeline (Dashboard ATS)', 'url' => route('ats.dashboard'), 'icon' => 'account_tree', 'keywords' => 'ats applicant tracking system pipeline board kanban'],
            ['title' => 'Buat / Config Stage (ATS)', 'url' => route('ats.stages'), 'icon' => 'settings', 'keywords' => 'buat setting config stage tahapan ats ats/buat stage setting/...'],
            ['title' => 'Buat / Tambah Kandidat (ATS)', 'url' => route('ats.candidate.manual'), 'icon' => 'person_add', 'keywords' => 'buat tambah kandidat manual ats pelamar ats/buat kandidat'],
            ['title' => 'Kandidat Blacklist', 'url' => route('ats.blacklist'), 'icon' => 'block', 'keywords' => 'blacklist daftar hitam kandidat diblokir'],
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
        
        return collect($this->features)->filter(function ($feature) use ($search) {
            return str_contains(strtolower($feature['title']), $search) || 
                   str_contains(strtolower($feature['keywords']), $search);
        })->take(5)->values()->toArray();
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
