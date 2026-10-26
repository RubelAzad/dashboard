<script>
    import X from '@lucide/svelte/icons/x';

    /**
     * Accessible modal built on <dialog>. Content is only rendered while open, so forms
     * inside start fresh every time. Escape, the close button and the backdrop all close it.
     */
    let { open = $bindable(false), title, description = null, size = 'md', children } = $props();

    const sizes = { sm: 'max-w-md', md: 'max-w-xl', lg: 'max-w-3xl', xl: 'max-w-5xl' };

    let dialog;

    $effect(() => {
        if (open && !dialog.open) dialog.showModal();
        if (!open && dialog.open) dialog.close();
    });

    // A click whose target is the <dialog> itself landed on the backdrop.
    function onBackdrop(event) {
        if (event.target === dialog) open = false;
    }
</script>

<dialog
    bind:this={dialog}
    class="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] {sizes[size]} flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm open:flex open:animate-slide-up dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
    aria-labelledby="modal-title"
    onclose={() => (open = false)}
    onclick={onBackdrop}
>
    {#if open}
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200/80 px-6 py-4 dark:border-slate-800">
            <div>
                <h2 id="modal-title" class="text-base font-semibold">{title}</h2>
                {#if description}
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{description}</p>
                {/if}
            </div>
            <button type="button" class="btn-icon -mr-2" aria-label="Close" onclick={() => (open = false)}><X /></button>
        </div>
        <div class="flex min-h-0 flex-1 flex-col">
            {@render children()}
        </div>
    {/if}
</dialog>
