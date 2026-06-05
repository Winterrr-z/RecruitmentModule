<div class="max-w-xl mx-auto px-4 py-16">
    <!-- Success Accept State -->
    @if ($statusResponse === 'success_accept')
        <div class="bg-surface-container-lowest p-8 rounded-lg shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 text-center space-y-6">
            <div class="w-16 h-16 bg-green-500/10 text-green-600 rounded-full flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[36px]">celebration</span>
            </div>
            <div>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-2">Selamat! Anda Telah Menerima Tawaran</h2>
                <p class="text-body-md text-on-surface-variant/70 leading-relaxed">
                    Terima kasih telah menerima tawaran bergabung bersama kami. Status Anda telah diperbarui menjadi <strong class="text-green-600 font-bold">Hired</strong>. Tim HR kami akan menghubungi Anda segera melalui email untuk langkah koordinasi dan proses onboarding selanjutnya.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all duration-200 active:scale-95 text-sm shadow-[0_4px_12px_rgba(107,56,212,0.18)] no-underline">
                    <span class="material-symbols-outlined">home</span>
                    <span>Kembali ke Portal Karir</span>
                </a>
            </div>
        </div>

    <!-- Success Reject State -->
    @elseif ($statusResponse === 'success_reject')
        <div class="bg-surface-container-lowest p-8 rounded-lg shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 text-center space-y-6">
            <div class="w-16 h-16 bg-gray-500/10 text-gray-600 rounded-full flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[36px]">info</span>
            </div>
            <div>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-2">Tawaran Telah Ditolak</h2>
                <p class="text-body-md text-on-surface-variant/70 leading-relaxed">
                    Anda telah menolak tawaran pekerjaan ini. Kami menghargai waktu dan keputusan Anda. Profil Anda akan disimpan secara aman di bank bakat kami untuk peluang karier lainnya di masa mendatang.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all duration-200 active:scale-95 text-sm shadow-[0_4px_12px_rgba(107,56,212,0.18)] no-underline">
                    <span class="material-symbols-outlined">home</span>
                    <span>Kembali ke Portal Karir</span>
                </a>
            </div>
        </div>

    <!-- Expired State -->
    @elseif ($statusResponse === 'expired')
        <div class="bg-surface-container-lowest p-8 rounded-lg shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 text-center space-y-6">
            <div class="w-16 h-16 bg-orange-500/10 text-orange-600 rounded-full flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[36px]">history</span>
            </div>
            <div>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-2">Tawaran Sudah Kedaluwarsa</h2>
                <p class="text-body-md text-on-surface-variant/70 leading-relaxed">
                    Batas waktu tanggapan 3 hari untuk surat penawaran ini telah habis. Masa berlaku tautan unik penawaran Anda telah kedaluwarsa dan tidak lagi dapat diakses. Silakan hubungi departemen HR kami apabila Anda merasa hal ini adalah kekeliruan.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all duration-200 active:scale-95 text-sm shadow-[0_4px_12px_rgba(107,56,212,0.18)] no-underline">
                    <span>Kembali ke Portal Karir</span>
                </a>
            </div>
        </div>

    <!-- Invalid Token State -->
    @elseif ($statusResponse === 'invalid')
        <div class="bg-surface-container-lowest p-8 rounded-lg shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 text-center space-y-6">
            <div class="w-16 h-16 bg-red-500/10 text-red-600 rounded-full flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[36px]">error</span>
            </div>
            <div>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-2">Tautan Tidak Valid</h2>
                <p class="text-body-md text-on-surface-variant/70 leading-relaxed">
                    Maaf, tautan penawaran yang Anda akses tidak valid atau sudah tidak aktif. Pastikan Anda mengeklik tautan yang dikirimkan secara resmi ke email Anda.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all duration-200 active:scale-95 text-sm shadow-[0_4px_12px_rgba(107,56,212,0.18)] no-underline">
                    <span>Kembali ke Portal Karir</span>
                </a>
            </div>
        </div>

    <!-- Active Form State -->
    @else
        <div class="bg-surface-container-lowest p-8 rounded-lg shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 space-y-8">
            <div class="text-center">
                <h2 class="font-headline-lg text-2xl text-on-surface mb-2">Surat Penawaran Pekerjaan</h2>
                <p class="text-body-md text-sm text-on-surface-variant/70">Tinjau tawaran posisi jabatan dan berikan keputusan Anda</p>
            </div>

            <!-- Profile Overview Box -->
            <div class="bg-surface-container-low/40 p-6 rounded-md border border-surface-container/40 space-y-4">
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Nama Pelamar</span>
                    <span class="text-body-md text-on-surface font-bold text-lg">{{ $candidate->nama }}</span>
                </div>
                
                <hr class="border-surface-container-high/40">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Jabatan Tumpuan</span>
                        <span class="text-body-md text-on-surface font-semibold">{{ $candidate->lowongan->jabatan }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Departemen</span>
                        <span class="text-body-md text-on-surface font-semibold">{{ $candidate->lowongan->departemen }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Tipe Kontrak</span>
                        <span class="text-body-md text-on-surface font-semibold capitalize">{{ $candidate->lowongan->tipe_kerja }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Penempatan</span>
                        <span class="text-body-md text-on-surface font-semibold capitalize">{{ $candidate->lowongan->lokasi }}</span>
                    </div>
                </div>
            </div>

            <!-- Letter Text Preview -->
            <div class="text-sm text-on-surface-variant/90 leading-relaxed space-y-3 max-h-60 overflow-y-auto p-4 border rounded-md bg-surface/30">
                <p>Yth. <strong>{{ $candidate->nama }}</strong>,</p>
                <p>Kami menyampaikan apresiasi setinggi-tingginya atas partisipasi Anda dalam rangkaian proses seleksi di perusahaan kami. Kami sangat terkesan dengan kapabilitas dan potensi kontribusi yang dapat Anda berikan.</p>
                <p>Melalui surat ini, kami secara resmi menawarkan Anda untuk bergabung bersama tim kami pada posisi jabatan di atas. Kami meyakini bahwa bakat Anda akan sangat mendukung visi perkembangan perusahaan.</p>
                <p>Dengan mengeklik tombol <strong>Terima Tawaran</strong> di bawah ini, Anda menyatakan kesediaan untuk melanjutkan ke tahap administrasi penerimaan karyawan baru. Jika Anda memilih <strong>Tolak Tawaran</strong>, anda menyatakan menolak menjadi karyawan baru kami.</p>
                <p>Salam hangat,</p>
                <p><strong>Team HR Recruitment</strong></p>
            </div>

            <!-- Expiration Alert Notice -->
            @if ($candidate->offering_token_expires_at)
                <div class="text-center text-xs text-error font-semibold py-2 bg-error/5 border border-error/20 rounded-md">
                    * Penawaran ini berlaku hingga {{ $candidate->offering_token_expires_at->format('d M Y, H:i') }} (3 hari sejak dikirim).
                </div>
            @endif

            <!-- Form Actions with wire/http dual capabilities -->
            <form action="{{ route('offering.respond', ['token' => $token]) }}" method="POST">
                @csrf
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Accept Button -->
                    <button type="submit" name="choice" value="terima" 
                            wire:click.prevent="handleResponse('terima')"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 h-12 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md transition-all duration-200 active:scale-95 shadow-[0_4px_12px_rgba(22,163,74,0.18)] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        <span>Terima Tawaran</span>
                    </button>

                    <!-- Reject Button -->
                    <button type="submit" name="choice" value="tolak" 
                            wire:click.prevent="handleResponse('tolak')"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-6 h-12 bg-red-600 hover:bg-red-700 text-white font-bold rounded-md transition-all duration-200 active:scale-95 shadow-[0_4px_12px_rgba(220,38,38,0.18)] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                        <span>Tolak Tawaran</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
