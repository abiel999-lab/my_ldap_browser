<x-filament-panels::page.simple>
    <div class="mx-auto w-full max-w-lg space-y-6">
        <div class="rounded-2xl border border-danger-500/20 bg-danger-500/5 p-6">
            <h1 class="text-2xl font-bold tracking-tight text-danger-600 dark:text-danger-400">
                Akses Ditolak
            </h1>

            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                Akun berhasil login melalui Keycloak, tetapi tidak memiliki hak akses ke Petra LDAP Dashboard.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5">
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <p>Group yang diwajibkan:</p>
                <div class="rounded-xl bg-gray-50 px-4 py-3 font-medium text-gray-900 dark:bg-white/10 dark:text-white">
                    {{ config('petra_sso.allowed_group') }}
                </div>

                @if (session('error'))
                    <div class="rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-danger-600 dark:text-danger-300">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <a
                    href="{{ route('filament.app.auth.login') }}"
                    class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500"
                >
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
