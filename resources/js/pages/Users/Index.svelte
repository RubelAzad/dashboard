<script>
    import { Link, router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import Pagination from '../../components/Pagination.svelte';
    import { can } from '../../lib/permissions';

    let { users, filters } = $props();

    // Seeded once from the server; the input owns the value after that.
    let search = $state(untrack(() => filters.search ?? ''));
    let timer;

    function onSearch() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            router.get('/users', search ? { search } : {}, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
    }

    function destroy(user) {
        if (confirm(`Delete ${user.name}? This cannot be undone.`)) {
            router.delete(`/users/${user.id}`, { preserveScroll: true });
        }
    }
</script>

<AdminLayout title="Users">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
            type="search"
            class="form-input mt-0 sm:max-w-xs"
            placeholder="Search name or email"
            aria-label="Search users"
            bind:value={search}
            oninput={onSearch}
        />
        {#if can('users.create')}
            <Link href="/users/create" class="btn-primary">New user</Link>
        {/if}
    </div>

    <div class="card">
        {#if users.data.length === 0}
            <p class="px-5 py-8 text-center text-sm text-slate-500">No users found.</p>
        {:else}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th class="px-5 py-2">Name</th>
                            <th class="px-5 py-2">Email</th>
                            <th class="px-5 py-2">Roles</th>
                            <th class="px-5 py-2">Status</th>
                            <th class="px-5 py-2">Joined</th>
                            <th class="px-5 py-2"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {#each users.data as user (user.id)}
                            <tr>
                                <td class="px-5 py-3 font-medium text-slate-900">{user.name}</td>
                                <td class="px-5 py-3 text-slate-600">{user.email}</td>
                                <td class="px-5 py-3 text-slate-600">{user.roles.join(', ') || '—'}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {user.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-slate-100 text-slate-600'}"
                                    >
                                        {user.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">{user.created_at}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-4">
                                        <Link href="/users/{user.id}" class="link-action">View</Link>
                                        {#if can('users.update')}
                                            <Link href="/users/{user.id}/edit" class="link-action">Edit</Link>
                                        {/if}
                                        {#if can('users.delete')}
                                            <button type="button" class="link-danger" onclick={() => destroy(user)}>Delete</button>
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
</AdminLayout>
