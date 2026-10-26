<script>
    import { page, useForm } from '@inertiajs/svelte';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import { applyAppearance, effectiveAppearance } from '../lib/appearance';
    import AppearancePicker from './AppearancePicker.svelte';
    import Toggle from './Toggle.svelte';

    /** Organization-wide default theme (Super Admin). Changes preview live until saved or discarded. */
    let { appearance, allowUserThemes } = $props();

    const form = useForm(() => ({ appearance: { ...appearance }, allow_user_themes: allowUserThemes }));

    function change(next) {
        form.appearance = next;
        applyAppearance(next);
    }

    function discard() {
        form.reset();
        applyAppearance(effectiveAppearance(page.props.theme));
    }

    function submit(event) {
        event.preventDefault();
        form.put('/settings/appearance', { preserveScroll: true });
    }
</script>

<form onsubmit={submit} class="card mt-6" novalidate>
    <div class="card-header">
        <div class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <Sparkles class="size-4.5" />
            </span>
            <div>
                <h2 class="card-title">Default appearance</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    The theme everyone sees, including the sign-in page. Changes preview live on this page.
                </p>
            </div>
        </div>
    </div>

    <div class="grid gap-8 px-6 py-6 lg:grid-cols-3">
        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                <Toggle
                    bind:checked={form.allow_user_themes}
                    label="Let users personalise"
                    description="People can pick their own design, colours and font from the Appearance panel."
                />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Users who already chose a personal theme keep it. Turning personalisation off makes everyone use this default.
            </p>
        </div>
        <div class="lg:col-span-2">
            <AppearancePicker value={form.appearance} onchange={change} />
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
        {#if form.isDirty}
            <span class="mr-auto text-xs text-slate-500 dark:text-slate-400">Previewing unsaved changes.</span>
            <button type="button" class="btn-secondary" onclick={discard}>Discard</button>
        {/if}
        <button type="submit" class="btn-primary" disabled={form.processing || !form.isDirty}>
            {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
            Save default
        </button>
    </div>
</form>
