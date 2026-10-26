<script>
    import Monitor from '@lucide/svelte/icons/monitor';
    import Moon from '@lucide/svelte/icons/moon';
    import Sun from '@lucide/svelte/icons/sun';
    import { setTheme, theme } from '../lib/theme.svelte.js';

    const options = [
        { mode: 'light', label: 'Light', icon: Sun },
        { mode: 'dark', label: 'Dark', icon: Moon },
        { mode: 'system', label: 'System', icon: Monitor },
    ];
</script>

<div class="inline-flex rounded-lg bg-slate-100 p-0.5 dark:bg-slate-800" role="radiogroup" aria-label="Theme">
    {#each options as option (option.mode)}
        <button
            type="button"
            role="radio"
            aria-checked={theme.mode === option.mode}
            title={option.label}
            class="flex size-7 items-center justify-center rounded-md transition-colors {theme.mode === option.mode
                ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'}"
            onclick={() => setTheme(option.mode)}
        >
            <option.icon class="size-3.5" />
            <span class="sr-only">{option.label}</span>
        </button>
    {/each}
</div>
