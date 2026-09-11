@if($guest && $qrCode)
<section class="py-16 px-6 tpl-surface">
    <div class="max-w-md mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-4">QR Code Kehadiran</p>
        <p class="text-sm tpl-muted mb-6">{{ $guest->name }}</p>
        <div class="inline-block p-4 border tpl-hairline tpl-panel">
            {!! $qrCode !!}
        </div>
        <p class="text-xs tpl-faint mt-4">Tunjukkan QR ini kepada panitia saat tiba di lokasi</p>
    </div>
</section>
@endif
