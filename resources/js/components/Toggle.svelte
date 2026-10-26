<script>
    /** Accessible switch backed by a checkbox. */
    let { checked = $bindable(false), label, description = null, disabled = false } = $props();
</script>

<label class="flex items-start justify-between gap-4 {disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'}">
    <span>
        <span class="block text-sm font-medium text-slate-900 dark:text-white">{label}</span>
        {#if description}
            <span class="mt-0.5 block text-sm text-slate-500 dark:text-slate-400">{description}</span>
        {/if}
    </span>
    <span class="relative mt-0.5 inline-flex shrink-0">
        <input type="checkbox" role="switch" class="peer sr-only" bind:checked {disabled} />
        <span
            class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-brand-600 peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/25 dark:bg-slate-700 dark:peer-checked:bg-brand-500"
        ></span>
        <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-5"></span>
    </span>
</label>
