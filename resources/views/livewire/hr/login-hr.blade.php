<div class="min-h-[calc(100vh-5rem)] flex items-center justify-center py-12 px-4 relative bg-cover bg-center" style="background-image: url('{{ asset('images/hr_login_bg.png') }}');">
    <!-- Dark overlay to ensure card stands out -->
    <div class="absolute inset-0 bg-surface/30 backdrop-blur-[2px]"></div>
    
    <div class="w-full max-w-md relative z-10">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(107,56,212,0.08)] border border-surface-container-high px-8 py-10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary/10 mb-4">
                    <span class="material-symbols-outlined text-primary text-[28px]">admin_panel_settings</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold leading-tight">
                    Masuk Portal HR
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Silakan masuk untuk mengelola sistem rekrutmen.
                </p>
            </div>

            {{-- Alert Error --}}
            @if ($authError)
                <div class="mb-5 p-4 rounded-xl bg-error/10 border border-error/20 flex items-start gap-3">
                    <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">error</span>
                    <div class="text-sm font-semibold text-error">
                        {{ $authError }}
                    </div>
                </div>
            @endif

            {{-- Alert Sisa Percobaan --}}
            @if ($attemptsLeft !== null && $attemptsLeft > 0)
                <div class="mb-5 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-[20px] shrink-0 mt-0.5">warning</span>
                    <div class="text-sm font-semibold text-amber-700">
                        Sisa {{ $attemptsLeft }} percobaan lagi sebelum akun Anda dikunci sementara.
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form wire:submit="login" class="space-y-5" novalidate>

                {{-- Email --}}
                <div>
                    <label for="login-email" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Alamat Email HR
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">mail</span>
                        <input id="login-email"
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

                {{-- Password --}}
                <div>
                    <label for="login-password" class="block text-sm font-semibold text-on-surface mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">lock</span>
                        <input id="login-password"
                               wire:model="password"
                               type="password"
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi Anda"
                               class="w-full h-12 pl-11 pr-4 rounded-full bg-surface-container-low border @error('password') border-error/60 bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all font-body-md text-on-surface placeholder:text-on-surface-variant/50">
                    </div>
                    @error('password')
                        <p class="mt-1.5 ml-4 text-xs text-error flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="login-remember"
                               wire:model="remember"
                               type="checkbox"
                               class="w-4 h-4 rounded text-primary focus:ring-primary/20 border-surface-container-high bg-surface-container-low">
                        <label for="login-remember" class="ml-2 text-sm text-on-surface-variant select-none cursor-pointer">
                            Ingat Saya
                        </label>
                    </div>
                    <a href="{{ route('hr.password.request') }}" class="text-sm text-primary font-semibold hover:underline no-underline transition-colors">
                        Lupa Password?
                    </a>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            id="btn-login"
                            wire:loading.attr="disabled"
                            class="w-full h-12 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="login">
                            Masuk Portal HR
                        </span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
