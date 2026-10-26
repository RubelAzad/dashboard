<script>
    import { page, router } from '@inertiajs/svelte';
    import Check from '@lucide/svelte/icons/check';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Lock from '@lucide/svelte/icons/lock';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import X from '@lucide/svelte/icons/x';
    import { applyAppearance, effectiveAppearance } from '../lib/appearance';
    import AppearancePicker from './AppearancePicker.svelte';
    import ThemeToggle from './ThemeToggle.svelte';

    /**
     * Slide-over where each user personalises their theme. Every change is applied at once and
     * saved to their account shortly after, so it follows them to other devices.
     */
    let { open = $bindable(false) } = $props();

    const theme = $derived(page.props.theme);
    const custom = $derived(theme.allowCustom && !!theme.mine);

    let draft = $state(null);
    let status = $state('idle'); // idle | saving | saved
    let timer;
    let dialog;

    $effect(() => {
        if (open && !dialog.open) {
            draft = { ...effectiveAppearance(theme) };
            dialog.showModal();
        }
        if (!open && dialog.open) dialog.close();
    });

    function change(next) {
        draft = next;
        applyAppearance(next);
        status = 'saving';
        clearTimeout(timer);
        timer = setTimeout(save, 450);
    }

    function save() {
        router.patch('/appearance', draft, {
            only: ['theme'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => (status = 'saved'),
            onError: () => (status = 'idle'),
        });
    }

    function reset() {
        clearTimeout(timer);
        router.delete('/appearance', {
            only: ['theme'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                draft = { ...theme.defaults };
                status = 'idle';
            },
        });
    }
</script>

<dialog
    bind:this={dialog}
    class="fixed inset-y-0 right-0 left-auto m-0 h-dvh max-h-dvh w-full max-w-md bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/30 backdrop:backdrop-blur-[2px] open:flex open:animate-slide-in-right dark:bg-slate-900 dark:text-slate-100"
    aria-labelledby="appearance-title"
    onclose={() => (open = false)}
    onclick={(event) => event.target === dialog && (open = false)}
>
    {#if open && draft}
        <div class="flex min-h-0 flex-1 flex-col">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200/80 px-6 py-5 dark:border-slate-800">
                <div>
                    <h2 id="appearance-title" class="text-base font-semibold tracking-tight">Appearance</h2>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        {theme.allowCustom ? 'Make the dashboard yours. Changes save automatically.' : 'Your organization uses a shared theme.'}
                    </p>
                </div>
                <button type="button" class="btn-icon -mr-2" aria-label="Close" onclick={() => (open = false)}><X /></button>
            </div>

            <div class="min-h-0 flex-1 space-y-7 overflow-y-auto px-6 py-6">
                <section class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-medium">Mode</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Light, dark or follow your device</p>
                    </div>
                    <ThemeToggle />
                </section>

                {#if theme.allowCustom}
                    <AppearancePicker value={draft} onchange={change} />
                {:else}
                    <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                        <Lock class="mt-0.5 size-4 shrink-0" />
                        An administrator has turned off personal themes. You can still switch between light and dark mode.
                    </div>
                {/if}
            </div>

            {#if theme.allowCustom}
                <div class="flex items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400" aria-live="polite">
                        {#if status === 'saving'}
                            <LoaderCircle class="size-3.5 animate-spin" /> Saving…
                        {:else if status === 'saved'}
                            <Check class="size-3.5 text-emerald-500" /> Saved to your account
                        {:else if custom}
                            Using your personal theme
                        {:else}
                            Using the organization default
                        {/if}
                    </span>
                    <button type="button" class="btn-ghost px-2.5 py-1.5" disabled={!custom && status !== 'saved'} onclick={reset}>
                        <RotateCcw /> Reset to default
                    </button>
                </div>
            {/if}
        </div>
    {/if}
</dialog>
