<script>
    import { page } from '@inertiajs/svelte';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import { untrack } from 'svelte';
    import { slide } from 'svelte/transition';
    import NavLink from './NavLink.svelte';

    /** Collapsible sidebar group. `items` are { label, href, icon } links already filtered by permission. */
    let { label, icon: Icon, items, collapsed = false, onnavigate = undefined } = $props();

    const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`) || page.url.startsWith(`${href}?`);
    const hasActive = $derived(items.some((item) => isActive(item.href)));

    // Start open when the current page lives inside this group.
    let open = $state(untrack(() => hasActive));
    const id = $derived(`nav-group-${label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`);
</script>

{#if collapsed}
    <!-- Icon-only sidebar on desktop: show the children directly; the full group is used everywhere else. -->
    <div class="hidden space-y-0.5 lg:block">
        {#each items as item (item.href)}
            <NavLink href={item.href} icon={item.icon} label={item.label} collapsed onclick={onnavigate} />
        {/each}
    </div>
{/if}

<div class={collapsed ? 'lg:hidden' : ''}>
    <button
        type="button"
        class="app-nav-link w-full py-2"
        style:color={hasActive ? 'var(--sb-fg-strong)' : null}
        aria-expanded={open}
        aria-controls={id}
        onclick={() => (open = !open)}
    >
        <Icon class="app-nav-icon size-4.5 shrink-0" style={hasActive ? 'color: var(--sb-icon-active)' : undefined} />
        <span class="flex-1 text-left">{label}</span>
        <ChevronDown class="app-nav-icon size-4 shrink-0 transition-transform duration-200 {open ? 'rotate-180' : ''}" />
    </button>

    {#if open}
        <div {id} transition:slide={{ duration: 180 }} class="app-sidebar-divider mt-0.5 ml-5.25 space-y-0.5 border-l pl-3">
            {#each items as item (item.href)}
                <NavLink href={item.href} icon={item.icon} label={item.label} nested onclick={onnavigate} />
            {/each}
        </div>
    {/if}
</div>
