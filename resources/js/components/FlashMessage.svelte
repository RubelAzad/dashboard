<script>
    import { page } from '@inertiajs/svelte';
    import CircleAlert from '@lucide/svelte/icons/circle-alert';
    import CircleCheck from '@lucide/svelte/icons/circle-check';
    import X from '@lucide/svelte/icons/x';
    import { untrack } from 'svelte';
    import { fly } from 'svelte/transition';

    /** Shows session flash messages as toasts that dismiss themselves. */
    let toasts = $state([]);
    let nextId = 0;

    function push(type, message) {
        const id = nextId++;
        toasts = [...toasts, { id, type, message }];
        setTimeout(() => dismiss(id), 5000);
    }

    function dismiss(id) {
        toasts = toasts.filter((toast) => toast.id !== id);
    }

    // Every visit delivers a fresh flash object, so this runs once per response.
    $effect(() => {
        const flash = page.props.flash ?? {};
        untrack(() => {
            if (flash.success) push('success', flash.success);
            if (flash.error) push('error', flash.error);
        });
    });
</script>

<div class="pointer-events-none fixed inset-x-4 top-4 z-60 flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6" aria-live="polite">
    {#each toasts as toast (toast.id)}
        <div
            transition:fly={{ y: -12, duration: 200 }}
            role={toast.type === 'error' ? 'alert' : 'status'}
            class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border bg-white p-4 shadow-lg shadow-slate-900/5 sm:w-96 dark:bg-slate-900 {toast.type ===
            'error'
                ? 'border-red-200 dark:border-red-500/30'
                : 'border-emerald-200 dark:border-emerald-500/30'}"
        >
            {#if toast.type === 'error'}
                <CircleAlert class="mt-0.5 size-5 shrink-0 text-red-500" />
            {:else}
                <CircleCheck class="mt-0.5 size-5 shrink-0 text-emerald-500" />
            {/if}
            <p class="flex-1 text-sm text-slate-700 dark:text-slate-200">{toast.message}</p>
            <button type="button" class="-m-1 rounded p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" aria-label="Dismiss" onclick={() => dismiss(toast.id)}>
                <X class="size-4" />
            </button>
        </div>
    {/each}
</div>
