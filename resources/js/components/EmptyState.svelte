<script>
    let { icon: Icon, title, description = null, children = undefined } = $props();
</script>

<div class="flex flex-col items-center px-6 py-14 text-center">
    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <Icon class="size-6" />
    </div>
    <p class="text-sm font-semibold text-slate-900 dark:text-white">{title}</p>
    {#if description}
        <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">{description}</p>
    {/if}
    {#if children}
        <div class="mt-5">{@render children()}</div>
    {/if}
</div>
