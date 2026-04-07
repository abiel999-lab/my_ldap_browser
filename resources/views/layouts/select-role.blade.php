<div>
    <form method="POST" action="{{ route('changerole') }}">
        @csrf
        <select name="role" onchange="this.form.submit()"
            class="w-full text-left text-sm px-2 py-1 border border-gray-300 rounded-md bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
            @foreach ($roles as $role)
                <option value="{{ Crypt::encryptString($role->role->kode) }}" @selected($role->role->kode === $selected)>
                    {{ ucfirst($role->role->nama) }}
                </option>
            @endforeach
        </select>
    </form>
</div>
