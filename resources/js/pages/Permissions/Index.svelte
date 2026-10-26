<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Permissions' };
</script>

<script>
    import { router } from '@inertiajs/svelte';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Lock from '@lucide/svelte/icons/lock';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import EmptyState from '../../components/EmptyState.svelte';
    import Modal from '../../components/Modal.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import PermissionForm from '../../components/PermissionForm.svelte';
    import { confirm } from '../../lib/confirm.svelte.js';
    import { can } from '../../lib/permissions';

    let { permissionGroups } = $props();

    // Add/rename modal. `editing` is null when adding.
    let modalOpen = $state(false);
    let editing = $state(null);
    let filter = $state('');

    const visibleGroups = $derived(
        Object.entries(permissionGroups)
            .map(([module, permissions]) => [module, permissions.filter((permission) => permission.name.includes(filter.trim().toLowerCase()))])
            .filter(([, permissions]) => permissions.length > 0),
    );

    function open(permission = null) {
        editing = permission;
        modalOpen = true;
    }

    async function destroy(permission) {
        const ok = await confirm({
            title: `Delete ${permission.name}?`,
            message: 'Roles holding this permission will lose it.',
            confirmLabel: 'Delete permission',
        });
        if (ok) router.delete(`/permissions/${permission.id}`, { preserveScroll: true });
    }
</script>

<PageHeader title="Permissions" description="Fine-grained abilities, grouped by module. Core permissions are used by the app and are locked.">
    {#snippet actions()}
        {#if can('permissions.create')}
            <button type="button" class="btn-primary" onclick={() => open()}><Plus /> New permission</button>
        {/if}
    {/snippet}
</PageHeader>

<div class="relative mb-6 sm:max-w-xs">
    <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
    <input type="search" class="form-input mt-0 pl-9" placeholder="Filter permissions…" aria-label="Filter permissions" bind:value={filter} />
</div>

{#if visibleGroups.length === 0}
    <div class="card">
        <EmptyState icon={KeyRound} title="No permissions found" description={filter ? 'Try a different filter.' : 'Add the first permission with New permission.'} />
    </div>
{:else}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {#each visibleGroups as [module, permissions] (module)}
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title capitalize">{module}</h2>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 tabular-nums dark:bg-slate-800 dark:text-slate-300">
                        {permissions.length}
                    </span>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    {#each permissions as permission (permission.id)}
                        <li class="group px-5 py-2.5">
                            <div class="flex min-h-8 items-center justify-between gap-3">
                                <span class="font-mono text-[13px] text-slate-700 dark:text-slate-300">{permission.name}</span>
                                {#if permission.core}
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400" title="Used by the application">
                                        <Lock class="size-3" /> Core
                                    </span>
                                {:else}
                                    <div class="flex gap-0.5 opacity-100 transition-opacity sm:opacity-0 sm:group-focus-within:opacity-100 sm:group-hover:opacity-100">
                                        {#if can('permissions.update')}
                                            <button type="button" class="btn-icon" title="Rename" aria-label="Rename {permission.name}" onclick={() => open(permission)}>
                                                <Pencil />
                                            </button>
                                        {/if}
                                        {#if can('permissions.delete')}
                                            <button type="button" class="btn-icon-danger" title="Delete" aria-label="Delete {permission.name}" onclick={() => destroy(permission)}>
                                                <Trash2 />
                                            </button>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </li>
                    {/each}
                </ul>
            </section>
        {/each}
    </div>
{/if}

<Modal bind:open={modalOpen} title={editing ? 'Rename permission' : 'New permission'} description={editing?.name} size="sm">
    <PermissionForm permission={editing} onclose={() => (modalOpen = false)} />
</Modal>
