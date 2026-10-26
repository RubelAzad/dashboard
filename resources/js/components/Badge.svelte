<script>
    let { tone = 'slate', dot = false, children } = $props();

    const tones = {
        slate: 'bg-slate-100 text-slate-700 ring-slate-500/10 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-400/20',
        brand: 'bg-brand-50 text-brand-700 ring-brand-600/15 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/25',
        green: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/25',
        amber: 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/25',
        red: 'bg-red-50 text-red-700 ring-red-600/15 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-400/25',
    };

    const dots = {
        slate: 'bg-slate-400',
        brand: 'bg-brand-500',
        green: 'bg-emerald-500',
        amber: 'bg-amber-500',
        red: 'bg-red-500',
    };
</script>

<span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset {tones[tone]}">
    {#if dot}
        <span class="size-1.5 rounded-full {dots[tone]}"></span>
    {/if}
    {@render children()}
</span>
