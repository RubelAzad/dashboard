<script>
    import { page, router, useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import InputError from '../../components/InputError.svelte';
    import { can } from '../../lib/permissions';

    let { permissionGroups } = $props();

    const createForm = useForm({ name: '' });

    let editingId = $state(null);
    let editName = $state('');
    const renameError = $derived(page.props.errors?.rename?.name);

    function create(event) {
        event.preventDefault();
        createForm.post('/permissions', {
            preserveScroll: true,
            onSuccess: () => createForm.reset(),
        });
    }

    function startEdit(permission) {
        editingId = permission.id;
        editName = permission.name;
    }

    function saveEdit(event, permission) {
        event.preventDefault();
        router.put(
            `/permissions/${permission.id}`,
            { name: editName },
            { preserveScroll: true, errorBag: 'rename', onSuccess: () => (editingId = null) },
        );
    }

    function destroy(permission) {
        if (confirm(`Delete permission ${permission.name}? Roles holding it will lose it.`)) {
            router.delete(`/permissions/${permission.id}`, { preserveScroll: true });
        }
    }
</script>

<AdminLayout title="Permissions">
    {#if can('permissions.create')}
        <form onsubmit={create} class="card mb-6 p-5" novalidate>
            <label for="permission-name" class="form-label">New permission</label>
            <div class="mt-1 flex flex-col gap-3 sm:flex-row">
                <input
                    id="permission-name"
                    type="text"
                    class="form-input mt-0 font-mono sm:max-w-sm"
                    placeholder="module.action, e.g. reports.export"
                    bind:value={createForm.name}
                />
                <button type="submit" class="btn-primary" disabled={createForm.processing}>Add permission</button>
            </div>
            <InputError message={createForm.errors.name} />
        </form>
    {/if}

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {#each Object.entries(permissionGroups) as [module, permissions] (module)}
            <section class="card">
                <h2 class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 capitalize">
                    {module}
                    <span class="ml-1 font-normal text-slate-500">({permissions.length})</span>
                </h2>
                <ul class="divide-y divide-slate-100">
                    {#each permissions as permission (permission.id)}
                        <li class="px-5 py-2.5">
                            {#if editingId === permission.id}
                                <form onsubmit={(event) => saveEdit(event, permission)} class="flex gap-2" novalidate>
                                    <input
                                        type="text"
                                        class="form-input mt-0 py-1 font-mono"
                                        aria-label="Permission name"
                                        bind:value={editName}
                                    />
                                    <button type="submit" class="link-action">Save</button>
                                    <button type="button" class="text-sm text-slate-500" onclick={() => (editingId = null)}>
                                        Cancel
                                    </button>
                                </form>
                                <InputError message={renameError} />
                            {:else}
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-sm text-slate-800">{permission.name}</span>
                                    {#if permission.core}
                                        <span class="text-xs text-slate-400" title="Used by the application">Core</span>
                                    {:else}
                                        <div class="flex gap-3">
                                            {#if can('permissions.update')}
                                                <button type="button" class="link-action" onclick={() => startEdit(permission)}>
                                                    Rename
                                                </button>
                                            {/if}
                                            {#if can('permissions.delete')}
                                                <button type="button" class="link-danger" onclick={() => destroy(permission)}>
                                                    Delete
                                                </button>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                        </li>
                    {/each}
                </ul>
            </section>
        {/each}
    </div>
</AdminLayout>
