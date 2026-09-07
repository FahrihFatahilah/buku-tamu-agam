@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center px-2 py-1.5 text-sm rounded transition-colors {{ $active ? 'bg-stone-100 text-stone-800 font-medium' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-50' }}"
>
    {{ $slot }}
</a>
