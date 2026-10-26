<script>
    import { Link, useForm } from '@inertiajs/svelte';
    import InputError from './InputError.svelte';
    import PermissionPicker from './PermissionPicker.svelte';

    /**
     * Shared create/edit form. `role` is null when creating.
     */
    let { role = null, permissionGroups, action, method = 'post', submitLabel } = $props();

    const form = useForm(() => ({
        name: role?.name ?? '',
        permissions: role?.permissions ?? [],
    }));

    function submit(event) {
        event.preventDefault();
        form[method](action, { preserveScroll: true });
    }
</script>

<form onsubmit={submit} class="card space-y-5 p-6" novalidate>
    <div class="max-w-md">
        <label for="name" class="form-label">Role name</label>
        <input id="name" type="text" class="form-input" bind:value={form.name} required />
        <InputError message={form.errors.name} />
    </div>

    <div>
        <p class="form-label">Permissions</p>
        <p class="mb-3 text-xs text-slate-500">Tick a module name to select all of its permissions.</p>
        <PermissionPicker groups={permissionGroups} bind:selected={form.permissions} />
        <InputError message={form.errors.permissions} />
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
        <Link href="/roles" class="btn-secondary">Cancel</Link>
        <button type="submit" class="btn-primary" disabled={form.processing}>{submitLabel}</button>
    </div>
</form>
