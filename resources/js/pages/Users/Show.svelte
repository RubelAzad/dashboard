<script>
    import { Link } from '@inertiajs/svelte';
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import { can } from '../../lib/permissions';

    let { user } = $props();
</script>

<AdminLayout title={user.name}>
    <div class="card">
        <dl class="divide-y divide-slate-100 text-sm">
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Name</dt>
                <dd class="text-slate-900 sm:col-span-2">{user.name}</dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Email</dt>
                <dd class="text-slate-900 sm:col-span-2">{user.email}</dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Status</dt>
                <dd class="sm:col-span-2">
                    <span
                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {user.is_active
                            ? 'bg-green-100 text-green-800'
                            : 'bg-slate-100 text-slate-600'}"
                    >
                        {user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Roles</dt>
                <dd class="text-slate-900 sm:col-span-2">{user.roles.join(', ') || '—'}</dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Permissions</dt>
                <dd class="flex flex-wrap gap-1.5 sm:col-span-2">
                    {#if user.roles.includes('Super Admin')}
                        <span class="text-slate-900">All permissions (Super Admin)</span>
                    {:else}
                        {#each user.permissions as permission (permission)}
                            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">{permission}</span>
                        {:else}
                            <span class="text-slate-500">None</span>
                        {/each}
                    {/if}
                </dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Created</dt>
                <dd class="text-slate-900 sm:col-span-2">{user.created_at}</dd>
            </div>
            <div class="grid gap-1 px-6 py-4 sm:grid-cols-3">
                <dt class="font-medium text-slate-500">Last updated</dt>
                <dd class="text-slate-900 sm:col-span-2">{user.updated_at}</dd>
            </div>
        </dl>
        <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
            <Link href="/users" class="btn-secondary">Back to users</Link>
            {#if can('users.update')}
                <Link href="/users/{user.id}/edit" class="btn-primary">Edit</Link>
            {/if}
        </div>
    </div>
</AdminLayout>
