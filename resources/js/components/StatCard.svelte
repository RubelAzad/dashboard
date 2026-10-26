<script>
    let { label, value, hint = null, icon: Icon, tone = 'brand' } = $props();

    const tones = {
        brand: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        green: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        sky: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
    };
</script>

<div class="card p-5 transition-shadow hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{label}</p>
        <span class="flex size-9 items-center justify-center rounded-lg {tones[tone]}">
            <Icon class="size-4.5" />
        </span>
    </div>
    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900 tabular-nums dark:text-white">{value}</p>
    {#if hint}
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{hint}</p>
    {/if}
</div>
