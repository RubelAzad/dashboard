<script>
    import { useForm } from '@inertiajs/svelte';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import InputError from './InputError.svelte';
    import PermissionPicker from './PermissionPicker.svelte';

    /**
     * Create/edit form rendered inside a Modal. `role` is null when creating.
     * `onclose` runs on Cancel and after a successful save.
     */
    let { role = null, permissionGroups, onclose } = $props();

    const form = useForm(() => ({
        name: role?.name ?? '',
        permissions: role?.permissions ?? [],
    }));

    const total = $derived(Object.values(permissionGroups).reduce((sum, group) => sum + group.length, 0));

    function submit(event) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => onclose() };
        role ? form.put(`/roles/${role.id}`, options) : form.post('/roles', options);
    }
</script>

<form onsubmit={submit} class="flex min-h-0 flex-1 flex-col" novalidate>
    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
        <div class="max-w-md">
            <label for="role-name" class="form-label">Role name</label>
            <input id="role-name" type="text" class="form-input" placeholder="e.g. Editor" aria-invalid={!!form.errors.name || undefined} bind:value={form.name} required />
            <InputError message={form.errors.name} />
        </div>

        <div class="mt-6 mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Permissions</h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Click a module header to toggle all of its permissions.</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="text-slate-500 tabular-nums dark:text-slate-400">{form.permissions.length} of {total}</span>
                <button type="button" class="link-action" onclick={() => (form.permissions = Object.values(permissionGroups).flat().map((p) => p.name))}>
                    Select all
                </button>
                <button type="button" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400" onclick={() => (form.permissions = [])}>
                    Clear
                </button>
            </div>
        </div>
        <PermissionPicker groups={permissionGroups} bind:selected={form.permissions} />
        <InputError message={form.errors.permissions} />
    </div>

    <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
        <button type="button" class="btn-secondary" onclick={onclose}>Cancel</button>
        <button type="submit" class="btn-primary" disabled={form.processing}>
            {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
            {role ? 'Save changes' : 'Create role'}
        </button>
    </div>
</form>
