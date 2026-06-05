@if (session()->has('message') || session()->has('success') || session()->has('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-10px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-[-10px]"
         class="fixed top-6 right-6 z-[9999] flex items-center w-full max-w-sm p-4 rounded-lg shadow-lg border {{ session()->has('error') ? 'bg-red-50 text-red-800 border-red-100' : 'bg-green-50 text-green-800 border-green-100' }}" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg {{ session()->has('error') ? 'text-red-500 bg-red-100' : 'text-green-500 bg-green-100' }}">
            <span class="material-symbols-outlined text-[20px]">{{ session()->has('error') ? 'error' : 'check_circle' }}</span>
        </div>
        <div class="ms-3 text-sm font-semibold">
            {{ session('message') ?? session('success') ?? session('error') }}
        </div>
        <button type="button" @click="show = false" class="ms-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8 transition-colors {{ session()->has('error') ? 'text-red-500 hover:bg-red-200' : 'text-green-500 hover:bg-green-200' }}" aria-label="Close">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
@endif
