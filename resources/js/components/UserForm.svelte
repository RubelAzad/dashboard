<script>
    import { useForm } from '@inertiajs/svelte';
    import Check from '@lucide/svelte/icons/check';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import InputError from './InputError.svelte';
    import PasswordInput from './PasswordInput.svelte';
    import Toggle from './Toggle.svelte';

    /**
     * Create/edit form rendered inside a Modal. `user` is null when creating.
     * `onclose` runs on Cancel and after a successful save.
     */
    let { user = null, roles, onclose } = $props();

    const form = useForm(() => ({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
        is_active: user?.is_active ?? true,
        roles: user?.roles ?? [],
    }));

    function submit(event) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => onclose(),
            onFinish: () => form.reset('password', 'password_confirmation'),
        };
        user ? form.put(`/users/${user.id}`, options) : form.post('/users', options);
    }

    function toggleRole(role) {
        form.roles = form.roles.includes(role) ? form.roles.filter((name) => name !== role) : [...form.roles, role];
    }
</script>

<form onsubmit={submit} class="flex min-h-0 flex-1 flex-col" novalidate>
    <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="form-label">Full name</label>
                <input id="name" type="text" class="form-input" aria-invalid={!!form.errors.name || undefined} bind:value={form.name} autocomplete="name" required />
                <InputError message={form.errors.name} />
            </div>
            <div>
                <label for="email" class="form-label">Email address</label>
                <input id="email" type="email" class="form-input" aria-invalid={!!form.errors.email || undefined} bind:value={form.email} autocomplete="email" required />
                <InputError message={form.errors.email} />
            </div>
            <div>
                <label for="password" class="form-label">
                    Password
                    {#if user}<span class="font-normal text-slate-400">(optional)</span>{/if}
                </label>
                <PasswordInput
                    id="password"
                    invalid={!!form.errors.password}
                    bind:value={form.password}
                    autocomplete="new-password"
                    placeholder={user ? 'Leave blank to keep' : 'At least 8 characters'}
                />
                <InputError message={form.errors.password} />
            </div>
            <div>
                <label for="password_confirmation" class="form-label">Confirm password</label>
                <PasswordInput id="password_confirmation" bind:value={form.password_confirmation} autocomplete="new-password" />
            </div>
        </div>

        <fieldset>
            <legend class="form-label">Roles</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                {#each roles as role (role)}
                    {@const selected = form.roles.includes(role)}
                    <button
                        type="button"
                        role="checkbox"
                        aria-checked={selected}
                        class="flex items-center gap-3 rounded-lg border px-3.5 py-2.5 text-left text-sm font-medium transition {selected
                            ? 'border-brand-500 bg-brand-50 text-brand-800 ring-1 ring-brand-500 dark:bg-brand-500/10 dark:text-brand-200'
                            : 'border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'}"
                        onclick={() => toggleRole(role)}
                    >
                        <span
                            class="flex size-4 items-center justify-center rounded border transition {selected
                                ? 'border-brand-600 bg-brand-600 text-white'
                                : 'border-slate-300 dark:border-slate-600'}"
                        >
                            {#if selected}<Check class="size-3 stroke-3" />{/if}
                        </span>
                        {role}
                    </button>
                {/each}
            </div>
            <InputError message={form.errors.roles} />
        </fieldset>

        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
            <Toggle bind:checked={form.is_active} label="Active account" description="Inactive users cannot sign in." />
            <InputError message={form.errors.is_active} />
        </div>
    </div>

    <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
        <button type="button" class="btn-secondary" onclick={onclose}>Cancel</button>
        <button type="submit" class="btn-primary" disabled={form.processing}>
            {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
            {user ? 'Save changes' : 'Create user'}
        </button>
    </div>
</form>
