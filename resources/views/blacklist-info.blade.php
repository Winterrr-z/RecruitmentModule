<x-guest-layout>
    <div class="min-h-[calc(100vh-20rem)] flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow-[0_20px_60px_rgba(107,56,212,0.08)] border border-surface-container-high px-8 py-10 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-error/10 mb-6">
                    <span class="material-symbols-outlined text-error text-[36px]">block</span>
                </div>
                
                <h1 class="font-headline-lg text-2xl text-on-surface font-bold leading-tight mb-4">
                    Pendaftaran Dibatasi
                </h1>
                
                <p class="font-body-md text-on-surface-variant leading-relaxed mb-8">
                    Anda tidak dapat melamar lowongan ini karena data Anda terdaftar dalam daftar hitam perusahaan.
                </p>
                
                <div class="flex flex-col gap-3">
                    <a href="{{ route('careers') }}" 
                       class="inline-flex items-center justify-center h-12 px-6 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all active:scale-[0.98] no-underline">
                        Kembali ke Halaman Lowongan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
