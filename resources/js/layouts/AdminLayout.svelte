<script>
    import { Link, page } from '@inertiajs/svelte';
    import FlashMessage from '../components/FlashMessage.svelte';
    import NavLink from '../components/NavLink.svelte';
    import { can } from '../lib/permissions';

    let { title = '', children } = $props();

    let sidebarOpen = $state(false);

    // Each link is shown only if the user holds `permission`. Hiding a link is cosmetic:
    // the matching route enforces the same permission on the server. `icon` is an SVG path.
    const navigation = [
        {
            label: 'Dashboard',
            href: '/dashboard',
            permission: 'dashboard.view',
            icon: 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
        },
        {
            label: 'Users',
            href: '/users',
            permission: 'users.view',
            icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0',
        },
        {
            label: 'Roles',
            href: '/roles',
            permission: 'roles.view',
            icon: 'M12 3l8 4v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4z',
        },
        {
            label: 'Permissions',
            href: '/permissions',
            permission: 'permissions.view',
            icon: 'M15 7a3 3 0 11-6 0 3 3 0 016 0zM12 10v11m0-4h4m-4 3h3',
        },
        {
            label: 'Settings',
            href: '/settings',
            permission: 'settings.view',
            icon: 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z',
        },
    ];

    const app = $derived(page.props.app);
    const user = $derived(page.props.auth.user);
    const visibleNavigation = $derived(navigation.filter((item) => can(item.permission)));
</script>

<svelte:head>
    <title>{title ? `${title} - ${app.name}` : app.name}</title>
</svelte:head>

<div class="min-h-screen">
    {#if sidebarOpen}
        <button
            type="button"
            class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
            aria-label="Close sidebar"
            onclick={() => (sidebarOpen = false)}
        ></button>
    {/if}

    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 transition-transform lg:translate-x-0 {sidebarOpen
            ? 'translate-x-0'
            : '-translate-x-full'}"
    >
        <div class="flex h-16 items-center gap-2 px-6">
            {#if app.logo}
                <img src={app.logo} alt="" class="h-8 w-8 rounded object-contain" />
            {/if}
            <span class="truncate text-lg font-semibold text-white">{app.name}</span>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-4">
            {#each visibleNavigation as item (item.href)}
                <NavLink href={item.href} onclick={() => (sidebarOpen = false)}>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                    </svg>
                    {item.label}
                </NavLink>
            {/each}
        </nav>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button
                type="button"
                class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                aria-label="Open sidebar"
                onclick={() => (sidebarOpen = true)}
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <h1 class="flex-1 truncate text-lg font-semibold text-slate-900">{title}</h1>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-medium text-slate-900">{user.name}</p>
                    <p class="text-xs text-slate-500">{user.roles.join(', ')}</p>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Log out
                </Link>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            <FlashMessage />
            {@render children()}
        </main>
    </div>
</div>
