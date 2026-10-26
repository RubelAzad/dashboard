<script>
    import { Link, router } from '@inertiajs/svelte';
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import { can } from '../../lib/permissions';

    let { roles } = $props();

    function destroy(role) {
        const warning = role.users_count ? ` ${role.users_count} user(s) will lose this role.` : '';
        if (confirm(`Delete role ${role.name}?${warning}`)) {
            router.delete(`/roles/${role.id}`, { preserveScroll: true });
        }
    }
</script>

<AdminLayout title="Roles">
    {#if can('roles.create')}
        <div class="mb-4 flex justify-end">
            <Link href="/roles/create" class="btn-primary">New role</Link>
        </div>
    {/if}

    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium tracking-wide text-slate-500 uppercase">
                <tr>
                    <th class="px-5 py-2">Role</th>
                    <th class="px-5 py-2">Users</th>
                    <th class="px-5 py-2">Permissions</th>
                    <th class="px-5 py-2"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                {#each roles as role (role.id)}
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-900">{role.name}</td>
                        <td class="px-5 py-3 text-slate-600">{role.users_count}</td>
                        <td class="px-5 py-3 text-slate-600">{role.protected ? 'All' : role.permissions_count}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            {#if role.protected}
                                <span class="text-xs text-slate-400">Protected</span>
                            {:else}
                                <div class="flex justify-end gap-4">
                                    {#if can('roles.update')}
                                        <Link href="/roles/{role.id}/edit" class="link-action">Edit</Link>
                                    {/if}
                                    {#if can('roles.delete')}
                                        <button type="button" class="link-danger" onclick={() => destroy(role)}>Delete</button>
                                    {/if}
                                </div>
                            {/if}
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
</AdminLayout>
