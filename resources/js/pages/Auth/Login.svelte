<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Sign in' };
</script>

<script>
    import { useForm } from '@inertiajs/svelte';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Mail from '@lucide/svelte/icons/mail';
    import InputError from '../../components/InputError.svelte';
    import PasswordInput from '../../components/PasswordInput.svelte';

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

<div class="mb-8">
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Welcome back</h1>
    <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Sign in to your account to continue.</p>
</div>

<form onsubmit={submit} class="space-y-5" novalidate>
    <div>
        <label for="email" class="form-label">Email address</label>
        <div class="relative">
            <Mail class="pointer-events-none absolute bottom-[11px] left-3 size-4 text-slate-400" />
            <input
                id="email"
                type="email"
                autocomplete="username"
                placeholder="you@company.com"
                class="form-input pl-9"
                aria-invalid={!!form.errors.email || undefined}
                bind:value={form.email}
                required
            />
        </div>
        <InputError message={form.errors.email} />
    </div>

    <div>
        <label for="password" class="form-label">Password</label>
        <PasswordInput
            id="password"
            autocomplete="current-password"
            placeholder="••••••••"
            invalid={!!form.errors.password}
            bind:value={form.password}
            required
        />
        <InputError message={form.errors.password} />
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
        <input type="checkbox" class="form-checkbox" bind:checked={form.remember} />
        Keep me signed in
    </label>

    <button type="submit" class="btn-primary w-full py-2.5" disabled={form.processing}>
        {#if form.processing}
            <LoaderCircle class="animate-spin" />
            Signing in…
        {:else}
            Sign in
            <ArrowRight />
        {/if}
    </button>
</form>

<p class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
    Accounts are created by an administrator. Contact yours if you need access.
</p>
