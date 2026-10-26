<script>
    /**
     * Checkbox grid of permissions grouped by module.
     * `groups` is { module: [{ name, action }] }; `selected` is a bindable array of names.
     */
    let { groups, selected = $bindable([]) } = $props();

    function toggleModule(permissions, checked) {
        const names = permissions.map((permission) => permission.name);
        selected = checked
            ? [...new Set([...selected, ...names])]
            : selected.filter((name) => !names.includes(name));
    }
</script>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    {#each Object.entries(groups) as [module, permissions] (module)}
        {@const allChecked = permissions.every((permission) => selected.includes(permission.name))}
        <fieldset class="rounded-md border border-slate-200 p-4">
            <legend class="px-1">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-900 capitalize">
                    <input
                        type="checkbox"
                        class="form-checkbox"
                        checked={allChecked}
                        onchange={(event) => toggleModule(permissions, event.currentTarget.checked)}
                    />
                    {module}
                </label>
            </legend>
            <div class="mt-2 space-y-2">
                {#each permissions as permission (permission.name)}
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" class="form-checkbox" value={permission.name} bind:group={selected} />
                        {permission.action}
                    </label>
                {/each}
            </div>
        </fieldset>
    {/each}
</div>
