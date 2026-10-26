<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Users' };
</script>

<script>
    import { Link, router } from '@inertiajs/svelte';
    import Eye from '@lucide/svelte/icons/eye';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Search from '@lucide/svelte/icons/search';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import Users from '@lucide/svelte/icons/users';
    import X from '@lucide/svelte/icons/x';
    import { untrack } from 'svelte';
    import Avatar from '../../components/Avatar.svelte';
    import Modal from '../../components/Modal.svelte';
    import UserForm from '../../components/UserForm.svelte';
    import Badge from '../../components/Badge.svelte';
    import EmptyState from '../../components/EmptyState.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import Pagination from '../../components/Pagination.svelte';
    import StatusBadge from '../../components/StatusBadge.svelte';
    import { confirm } from '../../lib/confirm.svelte.js';
    import { can } from '../../lib/permissions';

    let { users, filters, roles } = $props();

    // Create/edit modal. `editing` is null for a new user. Links elsewhere can open the
    // create modal with /users?create=1.
    let modalOpen = $state(new URLSearchParams(location.search).has('create') && can('users.create'));
    let editing = $state(null);

    function openCreate() {
        editing = null;
        modalOpen = true;
    }

    function openEdit(user) {
        editing = user;
        modalOpen = true;
    }

    // Seeded once from the server; the input owns the value after that.
    let search = $state(untrack(() => filters.search ?? ''));
    let timer;

    function runSearch() {
        router.get('/users', search ? { search } : {}, { preserveState: true, preserveScroll: true, replace: true });
    }

    function onSearch() {
        clearTimeout(timer);
        timer = setTimeout(runSearch, 300);
    }

    function clearSearch() {
        search = '';
        runSearch();
    }

    async function destroy(user) {
        const ok = await confirm({
            title: `Delete ${user.name}?`,
            message: 'This permanently removes the account and cannot be undone.',
            confirmLabel: 'Delete user',
        });
        if (ok) router.delete(`/users/${user.id}`, { preserveScroll: true });
    }
</script>

<PageHeader title="Users" description="Manage accounts, their roles and whether they can sign in.">
    {#snippet actions()}
        {#if can('users.create')}
            <button type="button" class="btn-primary" onclick={openCreate}><UserPlus /> New user</button>
        {/if}
    {/snippet}
</PageHeader>

<div class="card overflow-hidden">
    <div class="flex items-center gap-3 border-b border-slate-200/80 p-4 dark:border-slate-800">
        <div class="relative w-full sm:max-w-xs">
            <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
            <input
                type="search"
                class="form-input mt-0 pr-9 pl-9 [&::-webkit-search-cancel-button]:hidden"
                placeholder="Search name or email…"
                aria-label="Search users"
                bind:value={search}
                oninput={onSearch}
            />
            {#if search}
                <button type="button" class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-slate-400 hover:text-slate-600" aria-label="Clear search" onclick={clearSearch}>
                    <X class="size-3.5" />
                </button>
            {/if}
        </div>
    </div>

    {#if users.data.length === 0}
        <EmptyState
            icon={Users}
            title={filters.search ? 'No matching users' : 'No users yet'}
            description={filters.search ? `Nothing matches “${filters.search}”. Try a different name or email.` : 'Create the first account to get started.'}
        >
            {#if filters.search}
                <button type="button" class="btn-secondary" onclick={clearSearch}>Clear search</button>
            {/if}
        </EmptyState>
    {:else}
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th class="hidden md:table-cell">Joined</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    {#each users.data as user (user.id)}
                        <tr>
                            <td>
                                <Link prefetch href="/users/{user.id}" class="group flex items-center gap-3">
                                    <Avatar name={user.name} />
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">
                                            {user.name}
                                        </span>
                                        <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{user.email}</span>
                                    </span>
                                </Link>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    {#each user.roles as role (role)}
                                        <Badge tone={role === 'Super Admin' ? 'brand' : 'slate'}>{role}</Badge>
                                    {:else}
                                        <span class="text-slate-400">—</span>
                                    {/each}
                                </div>
                            </td>
                            <td><StatusBadge active={user.is_active} /></td>
                            <td class="hidden whitespace-nowrap text-slate-500 md:table-cell dark:text-slate-400">{user.created_at}</td>
                            <td>
                                <div class="flex justify-end gap-0.5">
                                    <Link prefetch href="/users/{user.id}" class="btn-icon" title="View" aria-label="View {user.name}"><Eye /></Link>
                                    {#if can('users.update') && user.can_manage}
                                        <button type="button" class="btn-icon" title="Edit" aria-label="Edit {user.name}" onclick={() => openEdit(user)}><Pencil /></button>
                                    {/if}
                                    {#if can('users.delete') && user.can_manage}
                                        <button type="button" class="btn-icon-danger" title="Delete" aria-label="Delete {user.name}" onclick={() => destroy(user)}>
                                            <Trash2 />
                                        </button>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}
    <Pagination paginator={users} />
</div>

<Modal
    bind:open={modalOpen}
    title={editing ? `Edit ${editing.name}` : 'New user'}
    description={editing ? editing.email : 'Create an account and choose what it can access.'}
    size="lg"
>
    <UserForm user={editing} {roles} onclose={() => (modalOpen = false)} />
</Modal>
