<div class="max-w-3xl mx-auto w-full">
    <!-- Content Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('hr.profile') }}" class="text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
            <h2 class="font-headline-lg text-headline-lg text-on-surface flex items-center gap-3">
                <span class="material-symbols-outlined text-[32px] text-primary">edit</span>
                <span>Edit Profil</span>
            </h2>
        </div>
        <p class="font-body-md text-body-md text-on-surface-variant/70 mt-1 ml-10">Perbarui data diri, info kontak, dan rincian pekerjaan Anda.</p>
    </div>

    <!-- Profile Form -->
    <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden">
        <form wire:submit.prevent="save" class="p-8 space-y-8" novalidate>
            <!-- Section 0: Foto Profil -->
            <div>
                <h3 class="text-title-md font-bold text-primary mb-4 pb-2 border-b border-surface-container">Foto Profil</h3>
                <div class="flex items-center gap-6">
                    <div class="relative w-24 h-24 rounded-full bg-surface-container flex items-center justify-center overflow-hidden border border-surface-container-high">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview Foto">
                        @elseif (auth()->user()->profile_photo_path)
                            <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" class="w-full h-full object-cover" alt="Foto Profil">
                        @else
                            <span class="material-symbols-outlined text-on-surface-variant text-[40px]">person</span>
                        @endif
                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-surface/50 flex items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label for="profile-photo" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Unggah Foto Baru</label>
                        <input type="file" id="profile-photo" wire:model="photo" accept="image/png, image/jpeg, image/jpg"
                               class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer @error('photo') border-error @enderror">
                        <p class="mt-2 text-xs text-on-surface-variant/70">Format yang didukung: PNG, JPG, JPEG. Ukuran maksimal: 10MB.</p>
                        @error('photo')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 1: Informasi Personal -->
            <div>
                <h3 class="text-title-md font-bold text-primary mb-4 pb-2 border-b border-surface-container">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div class="md:col-span-2">
                        <label for="profile-name" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nama Lengkap <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">badge</span>
                            <input type="text" id="profile-name" wire:model="name"
                                   placeholder="Nama lengkap Anda"
                                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('name') border-error @enderror">
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="profile-email" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Alamat Email <span class="text-error">*</span></label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">mail</span>
                            <input type="email" id="profile-email" wire:model="email"
                                   placeholder="email@example.com"
                                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('email') border-error @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="profile-phone" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nomor Telepon</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">call</span>
                            <input type="text" id="profile-phone" wire:model="phone_number"
                                   placeholder="Nomor telepon aktif"
                                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('phone_number') border-error @enderror">
                        </div>
                        @error('phone_number')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Informasi Jabatan -->
            <div>
                <h3 class="text-title-md font-bold text-primary mb-4 pb-2 border-b border-surface-container">Informasi Jabatan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Departemen -->
                    <div>
                        <label for="profile-dept" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Departemen</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">corporate_fare</span>
                            <input type="text" id="profile-dept" wire:model="departemen"
                                   placeholder="Contoh: Human Resources"
                                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('departemen') border-error @enderror">
                        </div>
                        @error('departemen')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Job Title / Jabatan -->
                    <div>
                        <label for="profile-job" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nama Jabatan (Job Title)</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">work</span>
                            <input type="text" id="profile-job" wire:model="job_title"
                                   placeholder="Contoh: HR Manager"
                                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('job_title') border-error @enderror">
                        </div>
                        @error('job_title')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button / Actions -->
            <div class="pt-6 border-t border-surface-container flex items-center justify-end gap-4">
                <a href="{{ route('hr.profile') }}" class="px-5 h-12 flex items-center justify-center border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm">
                    <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
