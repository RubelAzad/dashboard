<script>
    import { Link, page } from '@inertiajs/svelte';

    /** `nested` renders the smaller child style used inside NavGroup. Colours come from the design (app.css). */
    let { href, icon: Icon, label, collapsed = false, nested = false, onclick = undefined } = $props();

    const active = $derived(page.url === href || page.url.startsWith(`${href}/`) || page.url.startsWith(`${href}?`));
</script>

<Link
    prefetch
    {href}
    {onclick}
    title={collapsed ? label : undefined}
    class="app-nav-link {nested ? 'py-1.5' : 'py-2'} {collapsed ? 'lg:justify-center lg:px-0' : ''}"
    aria-current={active ? 'page' : undefined}
>
    {#if active}
        <span class="app-nav-indicator absolute inset-y-1.5 w-0.5 rounded-full {nested ? '-left-[13px]' : 'left-0'}"></span>
    {/if}
    <Icon class="app-nav-icon {nested ? 'size-4' : 'size-4.5'} shrink-0" />
    <span class={collapsed ? 'lg:sr-only' : ''}>{label}</span>
</Link>
