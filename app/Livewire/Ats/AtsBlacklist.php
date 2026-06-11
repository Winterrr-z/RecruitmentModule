<?php

namespace App\Livewire\Ats;

use App\Models\Blacklist;
use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
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
    public $name = '';
    public $email = '';
    public $phone = '';
    public $reason = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'reason' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.max' => 'Nama lengkap maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'phone.required' => 'Nomor telepon wajib diisi.',
        'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        'reason.required' => 'Alasan wajib diisi.',
        'reason.min' => 'Alasan minimal 5 karakter.',
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
        $this->name = $candidate->name;
        $this->email = $candidate->email;
        $this->phone = $candidate->phone;
        
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    public function save()
    {
        $this->validate();

        app(\App\Services\CandidateService::class)->blacklistDetails(
            $this->name,
            $this->email,
            $this->phone,
            $this->reason
        );

        $this->showModal = false;
        $this->resetForm();

        session()->flash('message', "Data blacklist baru untuk '{$this->name}' berhasil ditambahkan.");
    }

    public function deleteBlacklist($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        
        \DB::transaction(function () use ($blacklist) {
            $candidates = Candidate::where(fn($q) => $q->where('email', $blacklist->email)->orWhere('phone', $blacklist->phone))
                ->where('status', \App\Enums\CandidateStatus::BLACKLISTED)
                ->get();

            foreach ($candidates as $c) {
                $c->status = \App\Enums\CandidateStatus::REJECTED;
                $c->save();
            }

            $blacklist->delete();
        });

        session()->flash('message', "Data blacklist '{$blacklist->name}' berhasil dihapus.");
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->reason = '';
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    public function getCandidates()
    {
        if (strlen($this->candidateSearch) < 2) {
            return collect();
        }

        return Candidate::where(fn($q) => $q->where('name', 'like', '%' . $this->candidateSearch . '%')
            ->orWhere('email', 'like', '%' . $this->candidateSearch . '%'))
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $query = Blacklist::query();

        if ($this->search) {
            $query->where(fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%'));
        }

        $blacklistList = $query->orderBy('created_at', 'desc')->paginate(10);
        $pickerCandidates = $this->getCandidates();

        return view('livewire.ats.blacklist', [
            'blacklistList' => $blacklistList,
            'pickerCandidates' => $pickerCandidates,
        ]);
    }
}
