<script>
    import { Link, page } from '@inertiajs/svelte';
    import Activity from '@lucide/svelte/icons/activity';
    import FileClock from '@lucide/svelte/icons/file-clock';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import LayoutDashboard from '@lucide/svelte/icons/layout-dashboard';
    import Menu from '@lucide/svelte/icons/menu';
    import Palette from '@lucide/svelte/icons/palette';
    import PanelLeft from '@lucide/svelte/icons/panel-left';
    import ScrollText from '@lucide/svelte/icons/scroll-text';
    import Settings from '@lucide/svelte/icons/settings';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import UserCog from '@lucide/svelte/icons/user-cog';
    import Users from '@lucide/svelte/icons/users';
    import X from '@lucide/svelte/icons/x';
    import AppearancePanel from '../components/AppearancePanel.svelte';
    import ConfirmDialog from '../components/ConfirmDialog.svelte';
    import FlashMessage from '../components/FlashMessage.svelte';
    import NavGroup from '../components/NavGroup.svelte';
    import NavLink from '../components/NavLink.svelte';
    import UserMenu from '../components/UserMenu.svelte';
    import { can } from '../lib/permissions';

    let { title = '', children } = $props();

    // Each link is shown only if the user holds `permission`. Hiding a link is cosmetic:
    // the matching route enforces the same permission on the server. An item with
    // `children` renders as a collapsible group and is hidden when no child is visible.
    const sections = [
        {
            label: 'Overview',
            items: [{ label: 'Dashboard', href: '/dashboard', permission: 'dashboard.view', icon: LayoutDashboard }],
        },
        {
            label: 'Access control',
            items: [
                { label: 'Users', href: '/users', permission: 'users.view', icon: Users },
                {
                    label: 'Role & Permission',
                    icon: UserCog,
                    children: [
                        { label: 'User Role', href: '/roles', permission: 'roles.view', icon: ShieldCheck },
                        { label: 'User Permission', href: '/permissions', permission: 'permissions.view', icon: KeyRound },
                    ],
                },
            ],
        },
        {
            label: 'Monitoring',
            items: [
                {
                    label: 'Log',
                    icon: ScrollText,
                    children: [
                        { label: 'Activity Log', href: '/logs/activity', permission: 'activity-logs.view', icon: Activity },
                        { label: 'Audit Log', href: '/logs/audit', permission: 'audit-logs.view', icon: FileClock },
                    ],
                },
            ],
        },
        {
            label: 'System',
            items: [{ label: 'Settings', href: '/settings', permission: 'settings.view', icon: Settings }],
        },
    ];

    const COLLAPSED_KEY = 'sidebar-collapsed';

    let mobileOpen = $state(false);
    let appearanceOpen = $state(false);
    // Remembered across sessions in localStorage.
    let collapsed = $state(readCollapsed());

    function readCollapsed() {
        try {
            return localStorage.getItem(COLLAPSED_KEY) === '1';
        } catch {
            return false;
        }
    }

    function toggleCollapsed() {
        collapsed = !collapsed;
        try {
            localStorage.setItem(COLLAPSED_KEY, collapsed ? '1' : '0');
        } catch {
            // Ignore blocked storage.
        }
    }

    const app = $derived(page.props.app);
    const visibleSections = $derived(
        sections
            .map((section) => ({
                ...section,
                items: section.items
                    .map((item) => (item.children ? { ...item, children: item.children.filter((child) => can(child.permission)) } : item))
                    .filter((item) => (item.children ? item.children.length > 0 : can(item.permission))),
            }))
            .filter((section) => section.items.length > 0),
    );
</script>

<svelte:head>
    <title>{title ? `${title} · ${app.name}` : app.name}</title>
</svelte:head>

<svelte:window onkeydown={(event) => event.key === 'Escape' && (mobileOpen = false)} />

<div class="app-shell min-h-screen" data-collapsed={collapsed}>
    {#if mobileOpen}
        <button
            type="button"
            class="fixed inset-0 z-30 animate-fade-in bg-slate-950/60 backdrop-blur-sm lg:hidden"
            aria-label="Close sidebar"
            onclick={() => (mobileOpen = false)}
        ></button>
    {/if}

    <!-- Colours and shape come from the active design (see .app-sidebar in app.css). -->
    <aside class="app-sidebar lg:translate-x-0 {mobileOpen ? 'translate-x-0' : '-translate-x-full'}">
        <div class="flex h-16 shrink-0 items-center gap-3 px-5 {collapsed ? 'lg:justify-center lg:px-0' : ''}">
            <Link prefetch href="/dashboard" class="flex min-w-0 items-center gap-3">
                {#if app.logo}
                    <img src={app.logo} alt="" class="size-8 shrink-0 rounded-lg bg-white object-contain p-0.5" />
                {:else}
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-brand-400 to-brand-700 text-sm font-bold text-white shadow-lg shadow-brand-500/30 ring-1 ring-white/20"
                    >
                        {app.name.charAt(0).toUpperCase()}
                    </span>
                {/if}
                <span class="app-sidebar-title truncate text-[15px] font-semibold tracking-tight {collapsed ? 'lg:hidden' : ''}">{app.name}</span>
            </Link>
            <button type="button" class="app-nav-link ml-auto p-1 lg:hidden" aria-label="Close sidebar" onclick={() => (mobileOpen = false)}>
                <X class="size-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            {#each visibleSections as section (section.label)}
                <div>
                    <p class="app-nav-label mb-2 px-3 text-[11px] font-semibold tracking-wider uppercase {collapsed ? 'lg:sr-only' : ''}">
                        {section.label}
                    </p>
                    <div class="space-y-0.5">
                        {#each section.items as item (item.label)}
                            {#if item.children}
                                <NavGroup label={item.label} icon={item.icon} items={item.children} {collapsed} onnavigate={() => (mobileOpen = false)} />
                            {:else}
                                <NavLink href={item.href} icon={item.icon} label={item.label} {collapsed} onclick={() => (mobileOpen = false)} />
                            {/if}
                        {/each}
                    </div>
                </div>
            {/each}
        </nav>

        <div class="app-sidebar-divider hidden border-t p-3 lg:block">
            <button
                type="button"
                class="app-nav-link w-full py-2 {collapsed ? 'justify-center px-0' : ''}"
                aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                onclick={toggleCollapsed}
            >
                <PanelLeft class="app-nav-icon size-4.5 shrink-0" />
                {#if !collapsed}<span>Collapse</span>{/if}
            </button>
        </div>
    </aside>

    <div class="app-content">
        <header class="app-header sticky top-0 z-20 flex h-16 items-center gap-2 px-4 sm:px-6 lg:px-8">
            <button type="button" class="btn-icon -ml-1 lg:hidden" aria-label="Open sidebar" onclick={() => (mobileOpen = true)}>
                <Menu />
            </button>

            <p class="flex-1 truncate text-sm text-slate-500 dark:text-slate-400">
                <span class="hidden sm:inline">{app.name}</span>
                {#if title}
                    <span class="mx-1.5 hidden text-slate-300 sm:inline dark:text-slate-600">/</span>
                    <span class="font-medium text-slate-900 dark:text-white">{title}</span>
                {/if}
            </p>

            <button type="button" class="btn-icon size-9" title="Appearance" aria-label="Appearance" onclick={() => (appearanceOpen = true)}>
                <Palette class="size-[1.15rem]!" />
            </button>
            <UserMenu onappearance={() => (appearanceOpen = true)} />
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            {@render children()}
        </main>
    </div>
</div>

<AppearancePanel bind:open={appearanceOpen} />
<FlashMessage />
<ConfirmDialog />
