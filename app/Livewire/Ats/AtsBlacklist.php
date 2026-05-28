<?php

namespace App\Livewire\Ats;

use App\Models\Blacklist;
use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithPagination;

class AtsBlacklist extends Component
{
    use WithPagination;

    // Filters
    public $search = '';

    // Modal Control
    public $showModal = false;
    public $showCandidatePicker = false;
    public $candidateSearch = '';

    // Form inputs
    public $nama = '';
    public $email = '';
    public $telepon = '';
    public $alasan = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telepon' => 'required|string|max:20',
            'alasan' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama lengkap wajib diisi.',
        'nama.max' => 'Nama lengkap maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'telepon.required' => 'Nomor telepon wajib diisi.',
        'telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        'alasan.required' => 'Alasan wajib diisi.',
        'alasan.min' => 'Alasan minimal 5 karakter.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function toggleCandidatePicker()
    {
        $this->showCandidatePicker = !$this->showCandidatePicker;
        $this->candidateSearch = '';
    }

    public function selectCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->nama = $candidate->nama;
        $this->email = $candidate->email;
        $this->telepon = $candidate->telepon;
        
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    public function save()
    {
        $this->validate();

        \DB::transaction(function () {
            // Save to blacklist
            Blacklist::create([
                'nama' => $this->nama,
                'email' => $this->email,
                'telepon' => $this->telepon,
                'alasan' => $this->alasan,
            ]);

            // Automatically blacklist active candidates with this email to 'Blacklisted' and move to Final stage
            Candidate::where('email', $this->email)
                ->update([
                    'status' => 'Blacklisted',
                    'current_stage_id' => 2
                ]);
        });

        $this->showModal = false;
        $this->resetForm();

        session()->flash('message', "Data blacklist baru untuk '{$this->nama}' berhasil ditambahkan.");
    }

    public function deleteBlacklist($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        $blacklist->delete();

        session()->flash('message', "Data blacklist '{$blacklist->nama}' berhasil dihapus.");
    }

    public function resetForm()
    {
        $this->nama = '';
        $this->email = '';
        $this->telepon = '';
        $this->alasan = '';
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    public function getCandidates()
    {
        if (strlen($this->candidateSearch) < 2) {
            return collect();
        }

        return Candidate::where(fn($q) => $q->where('nama', 'like', '%' . $this->candidateSearch . '%')
            ->orWhere('email', 'like', '%' . $this->candidateSearch . '%'))
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $query = Blacklist::query();

        if ($this->search) {
            $query->where(fn($q) => $q->where('nama', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('telepon', 'like', '%' . $this->search . '%'));
        }

        $blacklistList = $query->orderBy('created_at', 'desc')->paginate(10);
        $pickerCandidates = $this->getCandidates();

        return view('livewire.ats.blacklist', [
            'blacklistList' => $blacklistList,
            'pickerCandidates' => $pickerCandidates,
        ])->layout('layouts.app');
    }
}
