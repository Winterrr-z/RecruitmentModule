<div>
    <x-breadcrumb :items="[['label' => 'Recruitment Request', 'url' => route('rr.index')], ['label' => isset($rrId) ? 'Edit' : 'Tambah', 'url' => null]]" />
    <!-- Content Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ $isEdit ? 'Edit Recruitment Request' : 'Buat Recruitment Request' }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant/70">{{ $isEdit ? 'Perbarui informasi lowongan pekerjaan yang ada.' : 'Buat lowongan pekerjaan baru berdasarkan rencana tenaga kerja (MPP) yang telah disetujui.' }}</p>
    </div>

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Main Form Fields (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card 1: Rencana Tenaga Kerja (MPP) -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0_20px_40px_rgba(107,56,212,0.01)]">
                    <div class="flex items-center gap-2 mb-6 pb-2 border-b border-surface-container-low">
                        <span class="material-symbols-outlined text-primary text-[24px]">assignment</span>
                        <h3 class="text-title-md font-title-md font-bold text-on-surface">Rencana Tenaga Kerja (MPP)</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="selectedMppId" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Pilih Manpower Plan <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="selectedMppId" wire:model.live="selectedMppId" @if($isReadOnly) disabled @endif 
                                        class="w-full h-12 px-6 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('selectedMppId') ring-2 ring-error/20 @enderror disabled:opacity-70 disabled:cursor-not-allowed appearance-none cursor-pointer">
                                    <option value="">-- Pilih Manpower Planning --</option>
                                    @foreach($mppsDropdown as $dropdownMpp)
                                        <option value="{{ $dropdownMpp->id }}">
                                            {{ $dropdownMpp->plan_name }} (Kuota: {{ $dropdownMpp->quota }} Orang)
                                        </option>
                                    @endforeach
                                </select>
                                @if(!$isReadOnly)
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">keyboard_arrow_down</span>
                                @else
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-primary bg-primary/10 px-2 py-1 rounded">Terkunci</span>
                                @endif
                            </div>
                            @error('selectedMppId')
                                <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Read-Only Grid (Otomatis Terisi dari MPP) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-surface-container-low">
                            <div>
                                <label class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Jabatan / Posisi</label>
                                <div class="relative">
                                    <input type="text" value="{{ $job_title }}" placeholder="Otomatis terisi dari MPP" disabled 
                                           class="w-full h-12 px-6 bg-surface-container-low/70 border-none rounded-md text-body-md text-on-surface-variant/80 cursor-not-allowed">
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[18px] pointer-events-none">lock</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Departemen</label>
                                <div class="relative">
                                    <input type="text" value="{{ $department }}" placeholder="Otomatis terisi dari MPP" disabled 
                                           class="w-full h-12 px-6 bg-surface-container-low/70 border-none rounded-md text-body-md text-on-surface-variant/80 cursor-not-allowed">
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[18px] pointer-events-none">lock</span>
                                </div>
                            </div>
                            <div>
                                <label for="quota" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Kuota Lowongan <span class="text-error">*</span></label>
                                <div class="relative">
                                    <input type="number" id="quota" wire:model="quota" min="1" placeholder="Masukkan kuota lowongan" 
                                           class="w-full h-12 px-6 bg-surface-container-low border-none rounded-md text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 transition-all @error('quota') ring-2 ring-error/20 @enderror">
                                </div>
                                @if($selectedMppId)
                                    <p class="text-xs text-on-surface-variant/60 mt-1">Isi kuota berdasarkan sisa kebutuhan MPP</p>
                                @endif
                                @error('quota')
                                    <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Expected Join Date</label>
                                <div class="relative">
                                    <input type="text" value="{{ $expected_join_date ? \Carbon\Carbon::parse($expected_join_date)->translatedFormat('d F Y') : '' }}" placeholder="Otomatis terisi dari MPP" disabled 
                                           class="w-full h-12 px-6 bg-surface-container-low/70 border-none rounded-md text-body-md text-on-surface-variant/80 cursor-not-allowed">
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[18px] pointer-events-none">lock</span>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Estimasi Gaji</label>
                                <div class="relative">
                                    @php
                                        $gajiFormatted = '';
                                        if ($estimated_salary_min && $estimated_salary_max) {
                                            $gajiFormatted = 'Rp ' . number_format($estimated_salary_min, 0, ',', '.') . ' - Rp ' . number_format($estimated_salary_max, 0, ',', '.');
                                        } elseif ($estimated_salary_min) {
                                            $gajiFormatted = 'Rp ' . number_format($estimated_salary_min, 0, ',', '.');
                                        } elseif ($estimated_salary_max) {
                                            $gajiFormatted = 'Rp ' . number_format($estimated_salary_max, 0, ',', '.');
                                        } elseif ($selectedMppId) {
                                            $gajiFormatted = 'Negosiasi';
                                        }
                                    @endphp
                                    <input type="text" value="{{ $gajiFormatted }}" placeholder="Otomatis terisi dari MPP" disabled 
                                           class="w-full h-12 px-6 bg-surface-container-low/70 border-none rounded-md text-body-md text-on-surface-variant/80 cursor-not-allowed font-semibold text-primary">
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-[18px] pointer-events-none">lock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Detail Lowongan -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0_20px_40px_rgba(107,56,212,0.01)]">
                    <div class="flex items-center gap-2 mb-6 pb-2 border-b border-surface-container-low">
                        <span class="material-symbols-outlined text-primary text-[24px]">description</span>
                        <h3 class="text-title-md font-title-md font-bold text-on-surface">Detail Lowongan</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="job_description" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Deskripsi Pekerjaan <span class="text-error">*</span></label>
                            <textarea id="job_description" wire:model="job_description" rows="8" 
                                      class="w-full px-6 py-4 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('job_description') ring-2 ring-error/20 @enderror" 
                                      placeholder="Tuliskan tugas, tanggung jawab utama, serta rincian pekerjaan untuk posisi ini..."></textarea>
                            @error('job_description')
                                <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="job_requirements" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Spesifikasi Kebutuhan (Opsional)</label>
                            <textarea id="job_requirements" wire:model="job_requirements" rows="6" 
                                      class="w-full px-6 py-4 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface" 
                                      placeholder="Contoh: Kualifikasi pendidikan minimal, tahun pengalaman kerja, keahlian teknis (spt: Laravel, Figma), dll."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Actions (1/3 width, Sticky) -->
            <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-28">
                
                <!-- Card 3: Pengaturan Publikasi -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0_20px_40px_rgba(107,56,212,0.01)]">
                    <div class="flex items-center gap-2 mb-6 pb-2 border-b border-surface-container-low">
                        <span class="material-symbols-outlined text-primary text-[24px]">settings</span>
                        <h3 class="text-title-md font-title-md font-bold text-on-surface">Pengaturan Publikasi</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label for="employment_type" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Tipe Kerja <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="employment_type" wire:model="employment_type" 
                                        class="w-full h-12 px-6 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('employment_type') ring-2 ring-error/20 @enderror appearance-none cursor-pointer">
                                    <option value="full-time">Full-time</option>
                                    <option value="contract">Contract</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">keyboard_arrow_down</span>
                            </div>
                            @error('employment_type')
                                <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Lokasi Kerja <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="location" wire:model="location" 
                                        class="w-full h-12 px-6 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('location') ring-2 ring-error/20 @enderror appearance-none cursor-pointer">
                                    <option value="remote">Remote</option>
                                    <option value="on-site">On-site</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">keyboard_arrow_down</span>
                            </div>
                            @error('location')
                                <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="application_deadline" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Application Deadline <span class="text-error">*</span></label>
                            <input type="date" id="application_deadline" wire:model="application_deadline" 
                                   class="w-full h-12 px-6 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('application_deadline') ring-2 ring-error/20 @enderror">
                            @error('application_deadline')
                                <p class="text-error text-xs mt-1 px-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tampilkan Gaji Checkbox -->
                        <div class="pt-2">
                            <label class="relative flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" id="show_salary" wire:model="show_salary" 
                                       class="w-5 h-5 rounded border-none bg-surface-container-low text-primary focus:ring-2 focus:ring-primary/20 cursor-pointer transition-all">
                                <span class="font-body-md text-on-surface text-sm">
                                    Tampilkan perkiraan gaji kepada kandidat
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Action Card -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0_20px_40px_rgba(107,56,212,0.01)] space-y-4">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 h-14 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        <span>{{ $isEdit ? 'Simpan Perubahan' : 'Buat Recruitment Request' }}</span>
                    </button>
                    <a href="{{ route('rr.index') }}" 
                       class="w-full flex items-center justify-center h-14 border border-surface-container-high bg-surface-container-low hover:bg-surface-container text-on-surface-variant font-bold rounded-md transition-colors active:scale-95">
                        Batal
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>