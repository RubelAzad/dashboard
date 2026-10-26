<script>
    import { Link, page } from '@inertiajs/svelte';

    let { href, children, onclick = undefined } = $props();

    const active = $derived(page.url === href || page.url.startsWith(`${href}/`) || page.url.startsWith(`${href}?`));
</script>

<Link
    {href}
    {onclick}
    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors {active
        ? 'bg-slate-800 text-white'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white'}"
    aria-current={active ? 'page' : undefined}
>
    {@render children()}
</Link>
