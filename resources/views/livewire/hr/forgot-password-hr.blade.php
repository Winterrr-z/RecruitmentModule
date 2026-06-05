<div class="min-h-screen flex items-center justify-center py-12 px-4 relative bg-cover bg-center" style="background-image: url('{{ asset('images/hr_login_bg.png') }}');">
    <!-- Dark overlay to ensure card stands out -->
    <div class="absolute inset-0 bg-surface/30 backdrop-blur-[2px]"></div>
    
    <div class="w-full max-w-md relative z-10">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(107,56,212,0.08)] border border-surface-container-high px-8 py-10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary/10 mb-4">
                    <span class="material-symbols-outlined text-primary text-[28px]">lock_reset</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold leading-tight">
                    Lupa Password
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Masukkan email Anda, kami akan mengirimkan link untuk reset password.
                </p>
            </div>

            {{-- Success Alert --}}
            @if ($status)
                <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 flex items-start gap-3">
                    <span class="material-symbols-outlined text-green-600 text-[20px] shrink-0 mt-0.5">check_circle</span>
                    <div class="text-sm font-semibold text-green-700">
                        {{ $status }}
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form wire:submit="sendResetLink" class="space-y-5" novalidate>

                {{-- Email --}}
                <div>
                    <label for="forgot-email" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Alamat Email HR
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">mail</span>
                        <input id="forgot-email"
                               wire:model="email"
                               type="email"
                               autocomplete="email"
                               placeholder="hr@humanfirst.com"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border @error('email') border-error/60 bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                    @error('email')
                        <p class="mt-1.5 ml-4 text-xs text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            id="btn-send-reset"
                            wire:loading.attr="disabled"
                            class="w-full h-12 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="sendResetLink">
                            Kirim Link Reset
                        </span>
                        <span wire:loading wire:target="sendResetLink" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-surface-container-high"></div>
                <span class="text-xs text-on-surface-variant/50 font-semibold uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-surface-container-high"></div>
            </div>

            {{-- Back to Login --}}
            <p class="text-center text-sm text-on-surface-variant">
                Ingat password Anda?
                <a href="{{ route('hr.login') }}"
                   class="text-primary font-bold hover:underline no-underline transition-colors">
                    Kembali ke Login
                </a>
            </p>

        </div>

    </div>
</div>
