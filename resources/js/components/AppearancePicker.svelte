<script>
    import Bell from '@lucide/svelte/icons/bell';
    import Check from '@lucide/svelte/icons/check';
    import House from '@lucide/svelte/icons/house';
    import Settings from '@lucide/svelte/icons/settings';
    import { OPTIONS } from '../lib/appearance';

    /** Theme option pickers. `value` is { design, accent, neutral, font, icons, radius, density }. */
    let { value, onchange } = $props();

    function pick(key, option) {
        onchange({ ...value, [key]: option });
    }
</script>

{#snippet heading(title, hint = null)}
    <div class="mb-2.5 flex items-baseline justify-between">
        <h3 class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400">{title}</h3>
        {#if hint}<span class="text-xs text-slate-400">{hint}</span>{/if}
    </div>
{/snippet}

{#snippet chip(key, option, body)}
    {@const selected = value[key] === option.value}
    <button
        type="button"
        role="radio"
        aria-checked={selected}
        class="relative flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm transition {selected
            ? 'border-brand-500 bg-brand-50 font-medium text-brand-800 ring-1 ring-brand-500 dark:bg-brand-500/10 dark:text-brand-200'
            : 'border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'}"
        onclick={() => pick(key, option.value)}
    >
        {@render body()}
    </button>
{/snippet}

<div class="space-y-7">
    <!-- Design -->
    <section>
        {@render heading('Design')}
        <div class="grid grid-cols-3 gap-3" role="radiogroup" aria-label="Design">
            {#each OPTIONS.design as option (option.value)}
                {@const selected = value.design === option.value}
                <button
                    type="button"
                    role="radio"
                    aria-checked={selected}
                    title={option.hint}
                    class="group text-left"
                    onclick={() => pick('design', option.value)}
                >
                    <!-- Miniature of the layout -->
                    <div
                        class="relative flex h-20 overflow-hidden rounded-xl border-2 p-1 transition {selected
                            ? 'border-brand-500 shadow-md shadow-brand-500/20'
                            : 'border-slate-200 group-hover:border-slate-300 dark:border-slate-700'}"
                        style:background={option.value === 'floating'
                            ? 'color-mix(in oklab, var(--color-brand-500) 10%, var(--color-slate-50))'
                            : 'var(--color-slate-50)'}
                    >
                        <div
                            class="flex w-1/3 flex-col gap-1 p-1.5 {option.value === 'floating' ? 'rounded-md' : 'rounded-sm'}"
                            style:background={option.value === 'midnight'
                                ? 'var(--color-slate-900)'
                                : option.value === 'daylight'
                                  ? '#fff'
                                  : 'linear-gradient(165deg, var(--color-brand-500), var(--color-brand-900))'}
                            style:border-right={option.value === 'daylight' ? '1px solid var(--color-slate-200)' : 'none'}
                        >
                            <span class="h-1.5 w-3/4 rounded-full" style:background={option.value === 'daylight' ? 'var(--color-brand-500)' : 'rgb(255 255 255 / .8)'}></span>
                            <span class="mt-1 h-1 w-full rounded-full" style:background={option.value === 'daylight' ? 'var(--color-slate-200)' : 'rgb(255 255 255 / .25)'}></span>
                            <span class="h-1 w-full rounded-full" style:background={option.value === 'daylight' ? 'var(--color-slate-200)' : 'rgb(255 255 255 / .25)'}></span>
                            <span class="h-1 w-2/3 rounded-full" style:background={option.value === 'daylight' ? 'var(--color-slate-200)' : 'rgb(255 255 255 / .25)'}></span>
                        </div>
                        <div class="flex flex-1 flex-col gap-1 p-1.5">
                            <span class="h-1.5 w-1/2 rounded-full bg-slate-300"></span>
                            <div class="grid flex-1 grid-cols-2 gap-1">
                                <span class="rounded-sm bg-white shadow-xs"></span>
                                <span class="rounded-sm bg-white shadow-xs"></span>
                            </div>
                        </div>
                        {#if selected}
                            <span class="absolute top-1.5 right-1.5 flex size-4 items-center justify-center rounded-full bg-brand-600 text-white">
                                <Check class="size-2.5 stroke-3" />
                            </span>
                        {/if}
                    </div>
                    <p class="mt-1.5 text-sm font-medium {selected ? 'text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-300'}">{option.label}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{option.hint}</p>
                </button>
            {/each}
        </div>
    </section>

    <!-- Accent colour -->
    <section>
        {@render heading('Accent colour', OPTIONS.accent.find((o) => o.value === value.accent)?.label)}
        <div class="flex flex-wrap gap-2.5" role="radiogroup" aria-label="Accent colour">
            {#each OPTIONS.accent as option (option.value)}
                {@const selected = value.accent === option.value}
                <button
                    type="button"
                    role="radio"
                    aria-checked={selected}
                    aria-label={option.label}
                    title={option.label}
                    class="flex size-9 items-center justify-center rounded-full ring-offset-2 ring-offset-white transition hover:scale-110 dark:ring-offset-slate-900 {selected
                        ? 'ring-2 ring-slate-900 dark:ring-white'
                        : ''}"
                    style:background={option.swatch}
                    onclick={() => pick('accent', option.value)}
                >
                    {#if selected}<Check class="size-4 stroke-3 text-white" />{/if}
                </button>
            {/each}
        </div>
    </section>

    <!-- Neutral tone -->
    <section>
        {@render heading('Neutral tone')}
        <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Neutral tone">
            {#each OPTIONS.neutral as option (option.value)}
                {#snippet body()}
                    <span class="size-3.5 rounded-full" style:background={option.swatch}></span>
                    {option.label}
                    <span class="text-xs text-slate-400">{option.hint}</span>
                {/snippet}
                {@render chip('neutral', option, body)}
            {/each}
        </div>
    </section>

    <!-- Font -->
    <section>
        {@render heading('Font')}
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" role="radiogroup" aria-label="Font">
            {#each OPTIONS.font as option (option.value)}
                {@const selected = value.font === option.value}
                <button
                    type="button"
                    role="radio"
                    aria-checked={selected}
                    class="rounded-lg border px-3 py-2.5 text-left transition {selected
                        ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:bg-brand-500/10'
                        : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800'}"
                    style:font-family={option.family}
                    onclick={() => pick('font', option.value)}
                >
                    <span class="block text-lg leading-none font-semibold text-slate-900 dark:text-white">Aa</span>
                    <span class="mt-1 block truncate text-xs text-slate-500 dark:text-slate-400">{option.label}</span>
                </button>
            {/each}
        </div>
    </section>

    <div class="grid gap-7 sm:grid-cols-2">
        <!-- Icon weight -->
        <section>
            {@render heading('Icons')}
            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Icon weight">
                {#each OPTIONS.icons as option (option.value)}
                    {#snippet body()}
                        <span class="flex flex-col items-center gap-1">
                            <span class="flex gap-1">
                                <House class="size-4" style="stroke-width: {option.stroke}" />
                                <Bell class="size-4" style="stroke-width: {option.stroke}" />
                                <Settings class="size-4" style="stroke-width: {option.stroke}" />
                            </span>
                            <span class="text-xs">{option.label}</span>
                        </span>
                    {/snippet}
                    {@render chip('icons', option, body)}
                {/each}
            </div>
        </section>

        <!-- Corner radius -->
        <section>
            {@render heading('Corners')}
            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Corner radius">
                {#each OPTIONS.radius as option (option.value)}
                    {#snippet body()}
                        <span class="flex flex-col items-center gap-1">
                            <span class="size-5 border-2 border-current border-r-0 border-b-0 opacity-70" style:border-top-left-radius={option.preview}></span>
                            <span class="text-xs">{option.label}</span>
                        </span>
                    {/snippet}
                    {@render chip('radius', option, body)}
                {/each}
            </div>
        </section>
    </div>

    <!-- Density -->
    <section>
        {@render heading('Density')}
        <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Density">
            {#each OPTIONS.density as option (option.value)}
                {#snippet body()}{option.label}{/snippet}
                {@render chip('density', option, body)}
            {/each}
        </div>
    </section>
</div>
