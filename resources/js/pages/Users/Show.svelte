<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = (props) => ({ title: props.user.name });
</script>

<script>
    import { router } from '@inertiajs/svelte';
    import Calendar from '@lucide/svelte/icons/calendar';
    import Clock from '@lucide/svelte/icons/clock';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Mail from '@lucide/svelte/icons/mail';
    import Pencil from '@lucide/svelte/icons/pencil';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Avatar from '../../components/Avatar.svelte';
    import Badge from '../../components/Badge.svelte';
    import Modal from '../../components/Modal.svelte';
    import UserForm from '../../components/UserForm.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import StatusBadge from '../../components/StatusBadge.svelte';
    import { confirm } from '../../lib/confirm.svelte.js';
    import { can } from '../../lib/permissions';

    let { user, roles } = $props();

    let editOpen = $state(false);

    const isSuperAdmin = $derived(user.roles.includes('Super Admin'));

    // Group "module.action" names by module for a tidier display.
    const permissionGroups = $derived(
        Object.entries(
            user.permissions.reduce((groups, name) => {
                const [module, action] = name.split('.');
                (groups[module] ??= []).push(action);
                return groups;
            }, {}),
        ),
    );

    async function destroy() {
        const ok = await confirm({
            title: `Delete ${user.name}?`,
            message: 'This permanently removes the account and cannot be undone.',
            confirmLabel: 'Delete user',
        });
        if (ok) router.delete(`/users/${user.id}`);
    }
</script>

<PageHeader title="User details" back={{ href: '/users', label: 'Users' }}>
    {#snippet actions()}
        {#if can('users.delete') && user.can_manage}
            <button type="button" class="btn-secondary text-red-600 dark:text-red-400" onclick={destroy}><Trash2 /> Delete</button>
        {/if}
        {#if can('users.update') && user.can_manage}
            <button type="button" class="btn-primary" onclick={() => (editOpen = true)}><Pencil /> Edit user</button>
        {/if}
    {/snippet}
</PageHeader>

<div class="grid gap-6 lg:grid-cols-3">
    <!-- Profile card -->
    <section class="card overflow-hidden">
        <div class="h-20 bg-linear-to-r from-brand-500 to-brand-700"></div>
        <div class="-mt-10 px-6 pb-6">
            <Avatar name={user.name} size="xl" class="ring-4 ring-white dark:ring-slate-900" />
            <h2 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{user.name}</h2>
            <div class="mt-1"><StatusBadge active={user.is_active} /></div>

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <dt><Mail class="size-4 text-slate-400" /><span class="sr-only">Email</span></dt>
                    <dd class="truncate text-slate-700 dark:text-slate-300">{user.email}</dd>
                </div>
                <div class="flex items-center gap-3">
                    <dt><Calendar class="size-4 text-slate-400" /><span class="sr-only">Created</span></dt>
                    <dd class="text-slate-700 dark:text-slate-300">Joined {user.created_at}</dd>
                </div>
                <div class="flex items-center gap-3">
                    <dt><Clock class="size-4 text-slate-400" /><span class="sr-only">Last updated</span></dt>
                    <dd class="text-slate-700 dark:text-slate-300">Updated {user.updated_at}</dd>
                </div>
            </dl>
        </div>
    </section>

    <div class="space-y-6 lg:col-span-2">
        <section class="card">
            <div class="card-header">
                <h2 class="card-title flex items-center gap-2"><ShieldCheck class="size-4 text-slate-400" /> Roles</h2>
            </div>
            <div class="flex flex-wrap gap-2 p-5">
                {#each user.roles as role (role)}
                    <Badge tone={role === 'Super Admin' ? 'brand' : 'slate'}>{role}</Badge>
                {:else}
                    <p class="text-sm text-slate-500 dark:text-slate-400">No roles assigned.</p>
                {/each}
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title flex items-center gap-2"><KeyRound class="size-4 text-slate-400" /> Effective permissions</h2>
                {#if !isSuperAdmin}
                    <span class="text-xs text-slate-500 dark:text-slate-400">{user.permissions.length} total</span>
                {/if}
            </div>
            <div class="p-5">
                {#if isSuperAdmin}
                    <div class="rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-800 dark:bg-brand-500/10 dark:text-brand-200">
                        Super Admins pass every permission check automatically.
                    </div>
                {:else if permissionGroups.length === 0}
                    <p class="text-sm text-slate-500 dark:text-slate-400">This user has no permissions.</p>
                {:else}
                    <dl class="grid gap-4 sm:grid-cols-2">
                        {#each permissionGroups as [module, actions] (module)}
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">{module}</dt>
                                <dd class="mt-1.5 flex flex-wrap gap-1.5">
                                    {#each actions as action (action)}
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{action}</span>
                                    {/each}
                                </dd>
                            </div>
                        {/each}
                    </dl>
                {/if}
            </div>
        </section>
    </div>
</div>

<Modal bind:open={editOpen} title="Edit {user.name}" description={user.email} size="lg">
    <UserForm {user} {roles} onclose={() => (editOpen = false)} />
</Modal>
