<script>
    import TriangleAlert from '@lucide/svelte/icons/triangle-alert';
    import { confirmState, settleConfirm } from '../lib/confirm.svelte.js';

    /** Renders dialogs requested through confirm() in lib/confirm.svelte.js. Mount once per layout. */
    let dialog;

    $effect(() => {
        if (confirmState.open && !dialog.open) dialog.showModal();
        if (!confirmState.open && dialog.open) dialog.close();
    });
</script>

<dialog
    bind:this={dialog}
    class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm open:animate-slide-up dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
    aria-labelledby="confirm-title"
    oncancel={() => settleConfirm(false)}
>
    <div class="flex gap-4 p-6">
        <div
            class="flex size-10 shrink-0 items-center justify-center rounded-full {confirmState.options.danger
                ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400'
                : 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400'}"
        >
            <TriangleAlert class="size-5" />
        </div>
        <div>
            <h2 id="confirm-title" class="text-base font-semibold">{confirmState.options.title}</h2>
            {#if confirmState.options.message}
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{confirmState.options.message}</p>
            {/if}
        </div>
    </div>
    <div class="flex justify-end gap-2 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60">
        <button type="button" class="btn-secondary" onclick={() => settleConfirm(false)}>Cancel</button>
        <button
            type="button"
            class={confirmState.options.danger ? 'btn-danger' : 'btn-primary'}
            onclick={() => settleConfirm(true)}
        >
            {confirmState.options.confirmLabel}
        </button>
    </div>
</dialog>
