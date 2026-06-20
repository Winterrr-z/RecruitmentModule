<?php

namespace App\Livewire\Ats;

use Livewire\Form;
use App\Models\Stage;

/**
 * Class StageConfigForm
 *
 * Livewire Form Object (kelas khusus untuk membungkus properti formulir)
 * digunakan oleh AtsStageConfig untuk memisahkan logika validasi dan form state.
 *
 * @package App\Livewire\Ats
 */
class StageConfigForm extends Form
{
    /** @var \App\Models\Stage|null Objek Stage saat dalam mode edit, null jika sedang tambah baru. */
    public ?Stage $stage = null;

    /** @var string Nama tahapan. */
    public $name = '';

    /** @var string Deskripsi tahapan. */
    public $description = '';

    /** @var bool Apakah tahapan ini membutuhkan form evaluasi (scorecard). */
    public $needs_scorecard = false;

    /** @var bool Apakah tahapan ini membutuhkan jadwal wawancara. */
    public $needs_schedule = false;

    /** @var array Daftar kriteria penilaian. */
    public $scorecardKriteria = [];

    /** @var string Tipe wawancara bawaan (online/offline/hybrid). */
    public $interview_type = 'online';

    /** @var string Lokasi luring (offline) bawaan. */
    public $default_location = '';

    /** @var string Tautan ruang virtual bawaan. */
    public $default_virtual_link = '';

    /**
     * Mengisi nilai form menggunakan data tahapan yang sudah ada (untuk keperluan Edit).
     *
     * @param \App\Models\Stage $stage
     */
    public function setStage(Stage $stage)
    {
        $this->stage = $stage;
        $this->name = $stage->name;
        $this->description = $stage->description;
        $this->needs_scorecard = (bool)$stage->needs_scorecard;
        $this->needs_schedule = (bool)$stage->needs_schedule;
        $this->scorecardKriteria = $stage->scorecard_criteria ?: [['criteria' => '', 'weight' => 0]];
        $this->interview_type = $stage->interview_type ?: 'online';
        $this->default_location = $stage->default_location ?: '';
        $this->default_virtual_link = $stage->default_virtual_link ?: '';
    }

    /**
     * Aturan validasi bawaan Laravel untuk formulir tahapan rekrutmen.
     */
    public function rules()
    {
        $stageId = $this->stage ? $this->stage->id : 'NULL';
        return [
            'name' => 'required|string|max:100|unique:stages,name,' . $stageId,
            'description' => 'nullable|string',
            'needs_scorecard' => 'boolean',
            'needs_schedule' => 'boolean',
        ];
    }

    /**
     * Pesan error khusus dalam bahasa Indonesia.
     */
    public function messages()
    {
        return [
            'name.required' => 'Nama stage wajib diisi.',
            'name.unique' => 'Nama stage sudah digunakan.',
            'name.max' => 'Nama stage maksimal 100 karakter.',
        ];
    }

    /**
     * Mengembalikan nilai formulir ke kondisi semula (kosong).
     */
    public function resetForm()
    {
        $this->stage = null;
        $this->name = '';
        $this->description = '';
        $this->needs_scorecard = false;
        $this->needs_schedule = false;
        $this->scorecardKriteria = [['criteria' => '', 'weight' => 0]];
        $this->interview_type = 'online';
        $this->default_location = '';
        $this->default_virtual_link = '';
    }
}
