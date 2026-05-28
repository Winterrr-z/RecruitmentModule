<div>
    <!-- Content Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('hr.profile') }}" class="text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
            <h2 class="font-headline-lg text-headline-lg text-on-surface flex items-center gap-3">
                <span class="material-symbols-outlined text-[32px] text-primary">lock</span>
                <span>Ubah Password</span>
            </h2>
        </div>
        <p class="font-body-md text-body-md text-on-surface-variant/70 mt-1 ml-10">Perbarui password akun HR Anda. Pastikan menggunakan password yang kuat.</p>
    </div>

    <!-- Password Form -->
    <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden max-w-xl">
        <form wire:submit.prevent="changePassword" class="p-8 space-y-6" novalidate>

            <!-- Password Requirements Info -->
            <div class="p-4 rounded-lg bg-primary/5 border border-primary/10">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5 shrink-0">info</span>
                    <div class="text-sm text-on-surface-variant">
                        <p class="font-semibold text-on-surface mb-1">Persyaratan Password:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            <li>Minimal 8 karakter</li>
                            <li>Mengandung huruf besar (A-Z)</li>
                            <li>Mengandung huruf kecil (a-z)</li>
                            <li>Mengandung angka (0-9)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Current Password -->
            <div>
                <label for="change-current" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Password Saat Ini <span class="text-error">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock_open</span>
                    <input type="password" id="change-current" wire:model="current_password"
                           placeholder="Masukkan password saat ini"
                           autocomplete="current-password"
                           class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('current_password') border-error @enderror">
                </div>
                @error('current_password')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Divider -->
            <div class="border-t border-surface-container"></div>

            <!-- New Password -->
            <div>
                <label for="change-password" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Password Baru <span class="text-error">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock</span>
                    <input type="password" id="change-password" wire:model="password"
                           placeholder="Min. 8 karakter, huruf besar, kecil, angka"
                           autocomplete="new-password"
                           class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('password') border-error @enderror">
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="change-password-confirm" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Konfirmasi Password Baru <span class="text-error">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock_clock</span>
                    <input type="password" id="change-password-confirm" wire:model="password_confirmation"
                           placeholder="Ulangi password baru"
                           autocomplete="new-password"
                           class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface">
                </div>
            </div>

            <!-- Submit Button / Actions -->
            <div class="pt-4 border-t border-surface-container flex items-center justify-end gap-4">
                <a href="{{ route('hr.profile') }}" class="px-5 h-12 flex items-center justify-center border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm">
                    <span wire:loading.remove wire:target="changePassword">Ubah Password</span>
                    <span wire:loading wire:target="changePassword" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
