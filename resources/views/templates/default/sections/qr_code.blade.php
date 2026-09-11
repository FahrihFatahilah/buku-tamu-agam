@if($guest && $qrCode)
<section class="py-16 px-6 bg-white">
    <div class="max-w-md mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-4">QR Code Kehadiran</p>
        <p class="text-sm text-stone-500 mb-6">{{ $guest->name }}</p>
        <div class="inline-block p-4 border border-stone-200 bg-white">
            {!! $qrCode !!}
        </div>
        <p class="text-stone-400 text-xs mt-4">Tunjukkan QR ini kepada panitia saat tiba di lokasi</p>
    </div>
</section>
@endif
