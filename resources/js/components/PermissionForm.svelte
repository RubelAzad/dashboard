<script>
    import { useForm } from '@inertiajs/svelte';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import InputError from './InputError.svelte';

    /**
     * Add/rename form rendered inside a Modal. `permission` is null when adding.
     * `onclose` runs on Cancel and after a successful save.
     */
    let { permission = null, onclose } = $props();

    const form = useForm(() => ({ name: permission?.name ?? '' }));

    function submit(event) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => onclose() };
        permission ? form.put(`/permissions/${permission.id}`, options) : form.post('/permissions', options);
    }
</script>

<form onsubmit={submit} class="flex min-h-0 flex-1 flex-col" novalidate>
    <div class="px-6 py-5">
        <label for="permission-name" class="form-label">Permission name</label>
        <input
            id="permission-name"
            type="text"
            class="form-input font-mono"
            placeholder="e.g. reports.export"
            aria-invalid={!!form.errors.name || undefined}
            bind:value={form.name}
        />
        <p class="form-hint">Use the format <span class="font-mono">module.action</span>. The module groups it on this page.</p>
        <InputError message={form.errors.name} />
    </div>

    <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
        <button type="button" class="btn-secondary" onclick={onclose}>Cancel</button>
        <button type="submit" class="btn-primary" disabled={form.processing}>
            {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
            {permission ? 'Rename' : 'Add permission'}
        </button>
    </div>
</form>
