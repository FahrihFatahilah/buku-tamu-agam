@if($guest)
<section id="section-qr-code" class="py-16 px-4 text-center" style="background:#1a0a0a;">
    <div class="max-w-sm mx-auto">
        <p style="font-family:'Playfair Display',serif;color:rgba(245,240,232,0.5);font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;margin-bottom:0.5rem;">QR Code Tamu</p>
        <h2 style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.5rem;margin-bottom:0.25rem;">{{ $guest->name }}</h2>
        <div style="width:2.5rem;height:1px;background:rgba(184,150,12,0.5);margin:1rem auto 1.5rem;"></div>

        <div style="display:inline-block;padding:1rem;background:#fff;border-radius:4px;">
            {!! preg_replace('/^<\?xml[^?]*\?>\s*/i', '', $qrCode) !!}
        </div>

        <p style="color:rgba(245,240,232,0.4);font-size:0.75rem;margin-top:1.25rem;line-height:1.6;">
            Tunjukkan QR code ini kepada panitia<br>saat tiba di lokasi acara.
        </p>
    </div>
</section>
@endif
