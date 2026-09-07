@extends('auth.layout')

@section('title', 'Masuk')

@section('content')
<h2 class="text-lg font-medium text-stone-800 mb-6">Masuk ke akun Anda</h2>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm text-stone-600 mb-1.5">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            required
            value="{{ old('email') }}"
            class="w-full px-3 py-2 border border-stone-300 text-stone-800 text-sm focus:outline-none focus:border-stone-500 transition-colors @error('email') border-red-400 @enderror"
        >
        @error('email')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm text-stone-600 mb-1.5">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
            class="w-full px-3 py-2 border border-stone-300 text-stone-800 text-sm focus:outline-none focus:border-stone-500 transition-colors @error('password') border-red-400 @enderror"
        >
        @error('password')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
            <input type="checkbox" name="remember" class="border-stone-300">
            Ingat saya
        </label>
    </div>

    <button
        type="submit"
        class="w-full bg-stone-800 text-white text-sm py-2.5 px-4 hover:bg-stone-700 transition-colors focus:outline-none focus:ring-2 focus:ring-stone-500 focus:ring-offset-2"
    >
        Masuk
    </button>
</form>
@endsection
