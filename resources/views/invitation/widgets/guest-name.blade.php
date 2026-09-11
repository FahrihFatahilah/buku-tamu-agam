@php $p = $node['props'] ?? []; @endphp
{{--
    Personalized greeting. The guest comes from the secure token resolved by
    InvitationResolver — query parameters can never override this identity.
--}}
@if($guest)
<div class="n-inner">
    @if(!empty($p['prefix']))
    <p class="text-sm opacity-60" data-edit-prop="prefix">{{ $p['prefix'] }}</p>
    @endif
    <p class="n-display" data-guest-name>{{ $guest->name }}</p>
</div>
@endif
