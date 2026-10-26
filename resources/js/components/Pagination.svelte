<script>
    import { Link } from '@inertiajs/svelte';

    /** Laravel LengthAwarePaginator payload. */
    let { paginator } = $props();
</script>

{#if paginator.last_page > 1}
    <nav class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-5 py-3 sm:flex-row" aria-label="Pagination">
        <p class="text-sm text-slate-600">
            Showing {paginator.from}–{paginator.to} of {paginator.total}
        </p>
        <div class="flex flex-wrap gap-1">
            {#each paginator.links as link, i (i)}
                {#if link.url}
                    <Link
                        href={link.url}
                        preserveScroll
                        class="rounded-md px-3 py-1.5 text-sm {link.active
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-700 hover:bg-slate-100'}"
                    >
                        <!-- Labels come from Laravel's pagination translations (e.g. "&laquo; Previous"). -->
                        {@html link.label}
                    </Link>
                {:else}
                    <span class="rounded-md px-3 py-1.5 text-sm text-slate-400">{@html link.label}</span>
                {/if}
            {/each}
        </div>
    </nav>
{/if}
