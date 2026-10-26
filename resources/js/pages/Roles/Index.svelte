<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Roles' };
</script>

<script>
    import { router } from '@inertiajs/svelte';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Lock from '@lucide/svelte/icons/lock';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Users from '@lucide/svelte/icons/users';
    import Badge from '../../components/Badge.svelte';
    import EmptyState from '../../components/EmptyState.svelte';
    import Modal from '../../components/Modal.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import RoleForm from '../../components/RoleForm.svelte';
    import { confirm } from '../../lib/confirm.svelte.js';
    import { can } from '../../lib/permissions';

    let { roles, permissionGroups } = $props();

    // Create/edit modal. `editing` is null for a new role. Links elsewhere can open the
    // create modal with /roles?create=1.
    let modalOpen = $state(new URLSearchParams(location.search).has('create') && can('roles.create'));
    let editing = $state(null);

    function open(role = null) {
        editing = role;
        modalOpen = true;
    }

    async function destroy(role) {
        const ok = await confirm({
            title: `Delete the ${role.name} role?`,
            message: role.users_count
                ? `${role.users_count} user(s) will lose this role and its permissions.`
                : 'No users currently have this role.',
            confirmLabel: 'Delete role',
        });
        if (ok) router.delete(`/roles/${role.id}`, { preserveScroll: true });
    }
</script>

<PageHeader title="Roles" description="Roles group permissions so you can grant access in one step.">
    {#snippet actions()}
        {#if can('roles.create')}
            <button type="button" class="btn-primary" onclick={() => open()}><Plus /> New role</button>
        {/if}
    {/snippet}
</PageHeader>

{#if roles.length === 0}
    <div class="card">
        <EmptyState icon={ShieldCheck} title="No roles yet" description="Create a role to start granting permissions." />
    </div>
{:else}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {#each roles as role (role.id)}
            <article class="card group flex flex-col p-5 transition-shadow hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div
                        class="flex size-10 items-center justify-center rounded-xl {role.protected
                            ? 'bg-linear-to-br from-brand-500 to-brand-700 text-white shadow-md shadow-brand-500/30'
                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'}"
                    >
                        <ShieldCheck class="size-5" />
                    </div>
                    {#if role.protected}
                        <Badge tone="brand"><Lock class="size-3" /> Protected</Badge>
                    {/if}
                </div>

                <h2 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">{role.name}</h2>

                <div class="mt-2 flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><Users class="size-4" /> {role.users_count} {role.users_count === 1 ? 'user' : 'users'}</span>
                    <span class="inline-flex items-center gap-1.5">
                        <KeyRound class="size-4" />
                        {role.protected ? 'All permissions' : `${role.permissions_count} permissions`}
                    </span>
                </div>

                <div class="mt-5 flex items-center justify-end gap-1 border-t border-slate-100 pt-4 dark:border-slate-800">
                    {#if role.protected}
                        <p class="mr-auto text-xs text-slate-400">Always has every permission.</p>
                    {:else}
                        {#if can('roles.update')}
                            <button type="button" class="btn-ghost px-2.5 py-1.5" onclick={() => open(role)}><Pencil /> Edit</button>
                        {/if}
                        {#if can('roles.delete')}
                            <button type="button" class="btn-icon-danger" title="Delete" aria-label="Delete {role.name}" onclick={() => destroy(role)}>
                                <Trash2 />
                            </button>
                        {/if}
                    {/if}
                </div>
            </article>
        {/each}
    </div>
{/if}

<Modal
    bind:open={modalOpen}
    title={editing ? `Edit role: ${editing.name}` : 'New role'}
    description={editing ? `Changes apply immediately to every user with the ${editing.name} role.` : 'Bundle permissions into a role you can assign to users.'}
    size="xl"
>
    <RoleForm role={editing} {permissionGroups} onclose={() => (modalOpen = false)} />
</Modal>
