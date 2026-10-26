<script>
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';

    /** `back` is an optional { href, label } link shown above the title. */
    let { title, description = null, back = null, actions = undefined } = $props();
</script>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        {#if back}
            <Link
                prefetch
                href={back.href}
                class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
            >
                <ArrowLeft class="size-4" />
                {back.label}
            </Link>
        {/if}
        <h1 class="truncate text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{title}</h1>
        {#if description}
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>
        {/if}
    </div>
    {#if actions}
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {@render actions()}
        </div>
    {/if}
</div>
