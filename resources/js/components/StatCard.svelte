<script>
    let { label, value, hint = null } = $props();
</script>

<div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{label}</p>
    <p class="mt-2 text-3xl font-semibold text-slate-900 tabular-nums">{value}</p>
    {#if hint}
        <p class="mt-1 text-xs text-slate-500">{hint}</p>
    {/if}
</div>
