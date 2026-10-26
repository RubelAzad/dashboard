<script>
    import { useForm } from '@inertiajs/svelte';
    import GuestLayout from '../../layouts/GuestLayout.svelte';
    import InputError from '../../components/InputError.svelte';

    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(event) {
        event.preventDefault();
        form.post('/login', {
            onFinish: () => form.reset('password'),
        });
    }
</script>

<GuestLayout title="Log in">
    <form onsubmit={submit} class="space-y-4" novalidate>
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input
                id="email"
                type="email"
                autocomplete="username"
                bind:value={form.email}
                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                required
            />
            <InputError message={form.errors.email} />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input
                id="password"
                type="password"
                autocomplete="current-password"
                bind:value={form.password}
                class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                required
            />
            <InputError message={form.errors.password} />
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" bind:checked={form.remember} class="rounded border-slate-300" />
            Remember me
        </label>

        <button
            type="submit"
            disabled={form.processing}
            class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
        >
            {form.processing ? 'Signing in...' : 'Log in'}
        </button>
    </form>
</GuestLayout>
