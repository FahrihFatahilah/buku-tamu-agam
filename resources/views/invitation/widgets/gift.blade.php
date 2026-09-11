@php
    $p = $node['props'] ?? [];
    $methods = $giftMethods;
@endphp
@if($methods->isNotEmpty())
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
        @if(!empty($p['note']))
        <p class="text-sm opacity-70 mt-3" data-edit-prop="note">{{ $p['note'] }}</p>
        @endif
    </div>

    <div class="space-y-3">
        @foreach($methods as $method)
        @include('invitation.widgets._gift-card', ['method' => $method, 'showCopy' => true])
        @endforeach
    </div>
</div>
@endif
