<script>
    import { router } from '@inertiajs/svelte';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import Search from '@lucide/svelte/icons/search';
    import { untrack } from 'svelte';
    import { eventMeta } from '../lib/logs';

    /**
     * Filter bar for the log pages. `extra` is an optional { key, label, options: [{ value, label }] } select.
     */
    let { url, filters, events, extra = null } = $props();

    // Seeded once from the server; the inputs own the values after that.
    let values = $state(untrack(() => ({ ...filters })));
    let timer;

    const active = $derived(Object.values(filters).some((value) => value !== ''));

    function apply() {
        clearTimeout(timer);
        const query = Object.fromEntries(Object.entries(values).filter(([, value]) => value !== ''));
        router.get(url, query, { preserveState: true, preserveScroll: true, replace: true });
    }

    function applySoon() {
        clearTimeout(timer);
        timer = setTimeout(apply, 300);
    }

    function reset() {
        values = Object.fromEntries(Object.keys(values).map((key) => [key, '']));
        apply();
    }
</script>

<div class="flex flex-col gap-3 border-b border-slate-200/80 p-4 lg:flex-row lg:items-center dark:border-slate-800">
    <div class="relative lg:w-72">
        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
        <input
            type="search"
            class="form-input mt-0 pl-9"
            placeholder="Search description, user or IP…"
            aria-label="Search logs"
            bind:value={values.search}
            oninput={applySoon}
        />
    </div>

    <div class="grid flex-1 grid-cols-2 gap-3 sm:flex sm:flex-wrap sm:items-center">
        {#if extra}
            <select class="form-input mt-0 sm:w-40" aria-label={extra.label} bind:value={values[extra.key]} onchange={apply}>
                <option value="">All {extra.label.toLowerCase()}</option>
                {#each extra.options as option (option.value)}
                    <option value={option.value}>{option.label}</option>
                {/each}
            </select>
        {/if}
        <select class="form-input mt-0 sm:w-44" aria-label="Event" bind:value={values.event} onchange={apply}>
            <option value="">All events</option>
            {#each events as event (event)}
                <option value={event}>{eventMeta(event).label}</option>
            {/each}
        </select>
        <input type="date" class="form-input mt-0 sm:w-40" aria-label="From date" max={values.to || undefined} bind:value={values.from} onchange={apply} />
        <input type="date" class="form-input mt-0 sm:w-40" aria-label="To date" min={values.from || undefined} bind:value={values.to} onchange={apply} />
        {#if active}
            <button type="button" class="btn-ghost col-span-2 sm:col-span-1" onclick={reset}><RotateCcw /> Reset</button>
        {/if}
    </div>
</div>
