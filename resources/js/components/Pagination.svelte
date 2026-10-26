<script>
    import { Link } from '@inertiajs/svelte';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';

    /** Laravel LengthAwarePaginator payload. */
    let { paginator } = $props();

    // Laravel's first and last links are Previous/Next; the rest are page numbers and "..." gaps.
    const previous = $derived(paginator.links[0]);
    const next = $derived(paginator.links[paginator.links.length - 1]);
    const pages = $derived(paginator.links.slice(1, -1));
</script>

{#if paginator.total > 0}
    <nav
        class="flex flex-col items-center justify-between gap-3 border-t border-slate-200/80 px-5 py-3 sm:flex-row dark:border-slate-800"
        aria-label="Pagination"
    >
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Showing <span class="font-medium text-slate-900 dark:text-white">{paginator.from}–{paginator.to}</span>
            of <span class="font-medium text-slate-900 dark:text-white">{paginator.total}</span>
        </p>
        {#if paginator.last_page > 1}
            <div class="flex items-center gap-1">
                {#if previous.url}
                    <Link prefetch href={previous.url} preserveScroll class="btn-icon" aria-label="Previous page"><ChevronLeft /></Link>
                {:else}
                    <span class="btn-icon opacity-40" aria-hidden="true"><ChevronLeft /></span>
                {/if}
                {#each pages as link, i (i)}
                    {#if link.url}
                        <Link
                            prefetch
                            href={link.url}
                            preserveScroll
                            aria-current={link.active ? 'page' : undefined}
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm font-medium tabular-nums transition-colors {link.active
                                ? 'bg-brand-600 text-white shadow-sm dark:bg-brand-500'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'}"
                        >
                            {link.label}
                        </Link>
                    {:else}
                        <span class="px-1 text-sm text-slate-400">{link.label}</span>
                    {/if}
                {/each}
                {#if next.url}
                    <Link prefetch href={next.url} preserveScroll class="btn-icon" aria-label="Next page"><ChevronRight /></Link>
                {:else}
                    <span class="btn-icon opacity-40" aria-hidden="true"><ChevronRight /></span>
                {/if}
            </div>
        {/if}
    </nav>
{/if}
