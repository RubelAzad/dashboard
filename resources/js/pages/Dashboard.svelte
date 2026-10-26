<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Dashboard' };
</script>

<script>
    import { Link, page } from '@inertiajs/svelte';
    import Activity from '@lucide/svelte/icons/activity';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Settings from '@lucide/svelte/icons/settings';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import UserCheck from '@lucide/svelte/icons/user-check';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import Users from '@lucide/svelte/icons/users';
    import { eventMeta, timeAgo, toneChip } from '../lib/logs';
    import Avatar from '../components/Avatar.svelte';
    import EmptyState from '../components/EmptyState.svelte';
    import StatCard from '../components/StatCard.svelte';
    import StatusBadge from '../components/StatusBadge.svelte';
    import { can } from '../lib/permissions';

    let { stats, roleBreakdown, recentUsers, recentActivity = null } = $props();

    const user = $derived(page.props.auth.user);
    const firstName = $derived(user.name.split(' ')[0]);
    const activeRate = $derived(stats.users ? Math.round((stats.activeUsers / stats.users) * 100) : 0);
    const assigned = $derived(roleBreakdown.reduce((sum, role) => sum + role.users, 0));

    const greeting = (() => {
        const hour = new Date().getHours();
        return hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';
    })();

    const roleColors = ['bg-brand-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-violet-500'];

    const quickActions = [
        { label: 'Add a user', href: '/users?create=1', permission: 'users.create', icon: UserPlus },
        { label: 'Create a role', href: '/roles?create=1', permission: 'roles.create', icon: ShieldCheck },
        { label: 'Manage permissions', href: '/permissions', permission: 'permissions.view', icon: KeyRound },
        { label: 'App settings', href: '/settings', permission: 'settings.view', icon: Settings },
    ];
</script>

<!-- Welcome banner -->
<section
    class="relative mb-6 overflow-hidden rounded-2xl bg-linear-to-br from-brand-600 via-brand-700 to-brand-900 p-6 text-white shadow-lg shadow-brand-900/20 sm:p-8"
>
    <div class="pointer-events-none absolute -top-24 -right-16 size-72 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/3 size-72 rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-brand-100">{greeting},</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{firstName} 👋</h1>
            <p class="mt-2 max-w-lg text-sm text-brand-100/90">Here's what's happening across your workspace today.</p>
        </div>
        {#if can('users.create')}
            <Link prefetch href="/users?create=1" class="btn bg-white text-brand-700 shadow-sm hover:bg-brand-50">
                <UserPlus />
                New user
            </Link>
        {/if}
    </div>
</section>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <StatCard label="Total users" value={stats.users} icon={Users} hint="All registered accounts" />
    <StatCard
        label="Active users"
        value={stats.activeUsers}
        icon={UserCheck}
        tone="green"
        hint="{stats.users - stats.activeUsers} inactive · {activeRate}% active"
    />
    <StatCard label="Roles" value={stats.roles} icon={ShieldCheck} tone="sky" hint="Including Super Admin" />
    <StatCard label="Permissions" value={stats.permissions} icon={KeyRound} tone="amber" hint="Across all modules" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <!-- Recent users -->
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Recent users</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">The latest accounts to join.</p>
                </div>
                {#if can('users.view')}
                    <Link prefetch href="/users" class="link-action inline-flex items-center gap-1">
                        View all <ArrowRight class="size-3.5" />
                    </Link>
                {/if}
            </div>

            {#if recentUsers.length === 0}
                <EmptyState icon={Users} title="No users yet" description="New accounts will show up here." />
            {:else}
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    {#each recentUsers as recent (recent.id)}
                        <li class="flex items-center gap-3 px-5 py-3.5">
                            <Avatar name={recent.name} />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{recent.name}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{recent.email}</p>
                            </div>
                            <div class="hidden text-right text-xs text-slate-500 sm:block dark:text-slate-400">{recent.created_at}</div>
                            <StatusBadge active={recent.is_active} />
                        </li>
                    {/each}
                </ul>
            {/if}
        </section>

        <!-- Recent activity (only sent to users who can read the activity log) -->
        {#if recentActivity}
            <section class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Recent activity</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Sign-ins and changes across the dashboard.</p>
                    </div>
                    <Link prefetch href="/logs/activity" class="link-action inline-flex items-center gap-1">
                        View log <ArrowRight class="size-3.5" />
                    </Link>
                </div>
                {#if recentActivity.length === 0}
                    <EmptyState icon={Activity} title="No activity yet" description="Sign-ins and changes will show up here." />
                {:else}
                    <ul class="px-5 py-2">
                        {#each recentActivity as log, i (log.id)}
                            {@const meta = eventMeta(log.event)}
                            <li class="relative flex gap-3 py-2.5">
                                {#if i < recentActivity.length - 1}
                                    <span class="absolute top-10 bottom-0 left-4 w-px bg-slate-200 dark:bg-slate-800" aria-hidden="true"></span>
                                {/if}
                                <span class="relative flex size-8 shrink-0 items-center justify-center rounded-full {toneChip[meta.tone]}">
                                    <meta.icon class="size-3.5" />
                                </span>
                                <div class="min-w-0 flex-1 pt-1">
                                    <p class="truncate text-sm text-slate-700 dark:text-slate-300">
                                        {#if log.causer}<span class="font-medium text-slate-900 dark:text-white">{log.causer}</span><span class="mx-1.5 text-slate-300 dark:text-slate-600">·</span>{/if}{log.description}
                                    </p>
                                </div>
                                <time datetime={log.created_at_iso} title={log.created_at} class="shrink-0 pt-1 text-xs whitespace-nowrap text-slate-500 dark:text-slate-400">
                                    {timeAgo(log.created_at_iso)}
                                </time>
                            </li>
                        {/each}
                    </ul>
                {/if}
            </section>
        {/if}
    </div>

    <div class="space-y-6">
        <!-- Users by role -->
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">Users by role</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">{assigned} assignments</span>
            </div>
            <div class="p-5">
                {#if assigned === 0}
                    <p class="text-sm text-slate-500 dark:text-slate-400">No roles assigned yet.</p>
                {:else}
                    <div class="flex h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        {#each roleBreakdown as role, i (role.name)}
                            {#if role.users > 0}
                                <div class={roleColors[i % roleColors.length]} style="width: {(role.users / assigned) * 100}%" title="{role.name}: {role.users}"></div>
                            {/if}
                        {/each}
                    </div>
                    <ul class="mt-4 space-y-2.5">
                        {#each roleBreakdown as role, i (role.name)}
                            <li class="flex items-center gap-2.5 text-sm">
                                <span class="size-2.5 rounded-full {roleColors[i % roleColors.length]}"></span>
                                <span class="flex-1 text-slate-700 dark:text-slate-300">{role.name}</span>
                                <span class="font-medium text-slate-900 tabular-nums dark:text-white">{role.users}</span>
                            </li>
                        {/each}
                    </ul>
                {/if}
            </div>
        </section>

        <!-- Quick actions -->
        {#if quickActions.some((action) => can(action.permission))}
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick actions</h2>
                </div>
                <div class="grid grid-cols-2 gap-2 p-3">
                    {#each quickActions as action (action.href)}
                        {#if can(action.permission)}
                            <Link
                                prefetch
                                href={action.href}
                                class="group flex flex-col gap-2 rounded-lg border border-slate-100 p-3 text-sm font-medium text-slate-700 transition hover:border-brand-200 hover:bg-brand-50/50 dark:border-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5"
                            >
                                <action.icon class="size-4.5 text-slate-400 transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-400" />
                                {action.label}
                            </Link>
                        {/if}
                    {/each}
                </div>
            </section>
        {/if}
    </div>
</div>
