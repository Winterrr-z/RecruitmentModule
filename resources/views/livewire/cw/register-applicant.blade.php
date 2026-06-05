<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-6 px-4">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(107,56,212,0.08)] border border-surface-container-high px-6 py-6">

            {{-- Header --}}
            <div class="text-center mb-6">
                <a href="{{ route('careers') }}" class="inline-flex items-center justify-center mb-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset(config('company.logo')) }}" alt="{{ config('company.name') }} Logo" class="h-12 w-auto">
                </a>
                <h1 class="font-headline-lg text-[24px] text-on-surface font-bold leading-tight">
                    Register to {{ config('company.name') }}
                </h1>
                <p class="font-body-md text-sm text-on-surface-variant mt-0.5">
                    Daftar dan mulai perjalanan karir Anda.
                </p>
            </div>

            {{-- Form --}}
            <form wire:submit="register" class="space-y-4" novalidate>

                {{-- Nama --}}
                <div>
                    <label for="reg-name" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">badge</span>
                        <input id="reg-name"
                               wire:model="name"
                               type="text"
                               autocomplete="name"
                               placeholder="Masukkan nama lengkap Anda"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border @error('name') border-error/60 bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                    @error('name')
                        <p class="mt-1.5 ml-4 text-xs text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="reg-email" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">mail</span>
                        <input id="reg-email"
                               wire:model="email"
                               type="email"
                               autocomplete="email"
                               placeholder="contoh@email.com"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border @error('email') border-error/60 bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                    @error('email')
                        <p class="mt-1.5 ml-4 text-xs text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="reg-password" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock</span>
                        <input id="reg-password"
                               wire:model="password"
                               type="password"
                               autocomplete="new-password"
                               placeholder="Min. 8 karakter, huruf besar, angka"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border @error('password') border-error/60 bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                    @error('password')
                        <p class="mt-1.5 ml-4 text-xs text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="reg-password-confirm" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock_reset</span>
                        <input id="reg-password-confirm"
                               wire:model="password_confirmation"
                               type="password"
                               autocomplete="new-password"
                               placeholder="Ulangi kata sandi Anda"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border border-transparent focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            id="btn-register"
                            wire:loading.attr="disabled"
                            class="w-full h-12 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="register">
                            Daftar Sekarang
                        </span>
                        <span wire:loading wire:target="register" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-surface-container-high"></div>
                <span class="text-xs text-on-surface-variant/50 font-semibold uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-surface-container-high"></div>
            </div>

            {{-- Link Login --}}
            <p class="text-center text-sm text-on-surface-variant">
                Sudah punya akun?
                <a href="{{ route('candidate.login') }}"
                   class="text-primary font-bold hover:underline no-underline transition-colors">
                    Masuk
                </a>
            </p>

        </div>

    </div>
</div>
