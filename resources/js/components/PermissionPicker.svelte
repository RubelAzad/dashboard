<script>
    import Check from '@lucide/svelte/icons/check';
    import Minus from '@lucide/svelte/icons/minus';

    /**
     * Checkbox grid of permissions grouped by module.
     * `groups` is { module: [{ name, action }] }; `selected` is a bindable array of names.
     */
    let { groups, selected = $bindable([]) } = $props();

    function toggleModule(permissions, checked) {
        const names = permissions.map((permission) => permission.name);
        selected = checked ? [...new Set([...selected, ...names])] : selected.filter((name) => !names.includes(name));
    }

    function toggle(name) {
        selected = selected.includes(name) ? selected.filter((item) => item !== name) : [...selected, name];
    }
</script>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    {#each Object.entries(groups) as [module, permissions] (module)}
        {@const count = permissions.filter((permission) => selected.includes(permission.name)).length}
        {@const all = count === permissions.length}
        <fieldset
            class="overflow-hidden rounded-xl border transition-colors {count
                ? 'border-brand-200 dark:border-brand-500/30'
                : 'border-slate-200 dark:border-slate-700'}"
        >
            <legend class="sr-only">{module}</legend>
            <button
                type="button"
                role="checkbox"
                aria-checked={all ? 'true' : count ? 'mixed' : 'false'}
                class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors {count
                    ? 'bg-brand-50/70 dark:bg-brand-500/10'
                    : 'bg-slate-50 dark:bg-slate-800/50'}"
                onclick={() => toggleModule(permissions, !all)}
            >
                <span
                    class="flex size-4 items-center justify-center rounded border transition {count
                        ? 'border-brand-600 bg-brand-600 text-white'
                        : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900'}"
                >
                    {#if all}<Check class="size-3 stroke-3" />{:else if count}<Minus class="size-3 stroke-3" />{/if}
                </span>
                <span class="flex-1 text-sm font-semibold text-slate-900 capitalize dark:text-white">{module}</span>
                <span class="text-xs text-slate-500 tabular-nums dark:text-slate-400">{count}/{permissions.length}</span>
            </button>
            <div class="flex flex-wrap gap-1.5 p-3">
                {#each permissions as permission (permission.name)}
                    {@const on = selected.includes(permission.name)}
                    <button
                        type="button"
                        role="checkbox"
                        aria-checked={on}
                        aria-label={permission.name}
                        class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1 font-mono text-xs transition {on
                            ? 'border-brand-500 bg-brand-600 text-white dark:bg-brand-500'
                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'}"
                        onclick={() => toggle(permission.name)}
                    >
                        {#if on}<Check class="size-3 stroke-3" />{/if}
                        {permission.action}
                    </button>
                {/each}
            </div>
        </fieldset>
    {/each}
</div>
