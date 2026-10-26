<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import InputError from './InputError.svelte';

    /**
     * Shared create/edit form. `user` is null when creating.
     */
    let { user = null, roles, action, method = 'post', submitLabel } = $props();

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
        form[method](action, {
            preserveScroll: true,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<form onsubmit={submit} class="card space-y-5 p-6" novalidate>
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="form-label">Name</label>
            <input id="name" type="text" class="form-input" bind:value={form.name} autocomplete="name" required />
            <InputError message={form.errors.name} />
        </div>
        <div>
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" class="form-input" bind:value={form.email} autocomplete="email" required />
            <InputError message={form.errors.email} />
        </div>
        <div>
            <label for="password" class="form-label">
                Password {#if user}<span class="font-normal text-slate-500">(leave blank to keep current)</span>{/if}
            </label>
            <input id="password" type="password" class="form-input" bind:value={form.password} autocomplete="new-password" />
            <InputError message={form.errors.password} />
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input
                id="password_confirmation"
                type="password"
                class="form-input"
                bind:value={form.password_confirmation}
                autocomplete="new-password"
            />
        </div>
    </div>

    <fieldset>
        <legend class="form-label">Roles</legend>
        <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
            {#each roles as role (role)}
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" class="form-checkbox" value={role} bind:group={form.roles} />
                    {role}
                </label>
            {/each}
        </div>
        <InputError message={form.errors.roles} />
    </fieldset>

    <div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" class="form-checkbox" bind:checked={form.is_active} />
            Active (inactive users cannot log in)
        </label>
        <InputError message={form.errors.is_active} />
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
        <Link href="/users" class="btn-secondary">Cancel</Link>
        <button type="submit" class="btn-primary" disabled={form.processing}>{submitLabel}</button>
    </div>
</form>
