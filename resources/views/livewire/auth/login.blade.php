<div class="card p-7">
    <h2 class="text-lg font-bold text-gray-800 mb-1">Masuk ke Akun</h2>
    <p class="text-sm text-gray-400 mb-6">Masukkan email dan password untuk mengakses dashboard</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="text-xs font-medium text-gray-500">Email</label>
            <input
                type="email"
                wire:model="email"
                autofocus
                autocomplete="username"
                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
            >
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500">Password</label>
            <input
                type="password"
                wire:model="password"
                autocomplete="current-password"
                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
            >
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-500">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-klinik-green focus:ring-klinik-green">
            Ingat saya
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="w-full text-sm font-medium text-white bg-klinik-blue py-2.5 rounded-lg hover:bg-klinik-blue-dark disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>

    <p class="text-xs text-gray-400 text-center mt-6">
        Akun demo: <strong>admin@simklinik.test</strong> / <strong>password</strong>
    </p>
</div>
