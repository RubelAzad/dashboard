<script>
    import AdminLayout from '../layouts/AdminLayout.svelte';
    import StatCard from '../components/StatCard.svelte';

    let { stats, recentUsers } = $props();
</script>

<AdminLayout title="Dashboard">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Total users" value={stats.users} />
        <StatCard label="Active users" value={stats.activeUsers} hint={`${stats.users - stats.activeUsers} inactive`} />
        <StatCard label="Roles" value={stats.roles} />
        <StatCard label="Permissions" value={stats.permissions} />
    </div>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <h2 class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900">Recent users</h2>

        {#if recentUsers.length === 0}
            <p class="px-5 py-6 text-sm text-slate-500">No users yet.</p>
        {:else}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th class="px-5 py-2">Name</th>
                            <th class="px-5 py-2">Email</th>
                            <th class="px-5 py-2">Status</th>
                            <th class="px-5 py-2">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {#each recentUsers as user (user.id)}
                            <tr>
                                <td class="px-5 py-3 font-medium text-slate-900">{user.name}</td>
                                <td class="px-5 py-3 text-slate-600">{user.email}</td>
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
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </section>
</AdminLayout>
