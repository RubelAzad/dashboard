<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Settings' };
</script>

<script>
    import { useForm } from '@inertiajs/svelte';
    import Image from '@lucide/svelte/icons/image';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Lock from '@lucide/svelte/icons/lock';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Upload from '@lucide/svelte/icons/upload';
    import DefaultAppearanceForm from '../../components/DefaultAppearanceForm.svelte';
    import InputError from '../../components/InputError.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import { can } from '../../lib/permissions';

    let { settings, timezones, appearance, allowUserThemes } = $props();

    // File uploads must be sent as multipart POST, so PUT is spoofed via _method.
    const form = useForm(() => ({
        _method: 'put',
        app_name: settings.app_name,
        timezone: settings.timezone,
        logo: null,
        remove_logo: false,
    }));

    let fileInput;
    let preview = $state(null);
    const editable = can('settings.update');

    const shownLogo = $derived(preview ?? (form.remove_logo ? null : settings.logo_url));

    function pickLogo(event) {
        const file = event.currentTarget.files[0] ?? null;
        form.logo = file;
        form.remove_logo = false;
        if (preview) URL.revokeObjectURL(preview);
        preview = file ? URL.createObjectURL(file) : null;
    }

    function removeLogo() {
        form.logo = null;
        if (preview) URL.revokeObjectURL(preview);
        preview = null;
        if (fileInput) fileInput.value = '';
        form.remove_logo = !!settings.logo_url;
    }

    function submit(event) {
        event.preventDefault();
        form.post('/settings', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('logo', 'remove_logo');
                if (fileInput) fileInput.value = '';
                if (preview) URL.revokeObjectURL(preview);
                preview = null;
            },
        });
    }
</script>

{#snippet section(title, description, body)}
    <div class="grid gap-6 px-6 py-6 lg:grid-cols-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{title}</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>
        </div>
        <div class="lg:col-span-2">{@render body()}</div>
    </div>
{/snippet}

<PageHeader title="Settings" description="Branding and regional preferences for the whole dashboard." />

{#if !editable}
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
        <Lock class="size-4 shrink-0" />
        You can view these settings but not change them.
    </div>
{/if}

<form onsubmit={submit} class="card" novalidate>
    <fieldset disabled={!editable} class="divide-y divide-slate-200/80 dark:divide-slate-800">
        {#snippet general()}
            <div class="space-y-5">
                <div>
                    <label for="app_name" class="form-label">Application name</label>
                    <input id="app_name" type="text" class="form-input max-w-md" aria-invalid={!!form.errors.app_name || undefined} bind:value={form.app_name} required />
                    <p class="form-hint">Shown in the sidebar, the sign-in page and browser tabs.</p>
                    <InputError message={form.errors.app_name} />
                </div>
                <div>
                    <label for="timezone" class="form-label">Timezone</label>
                    <select id="timezone" class="form-input max-w-md" bind:value={form.timezone}>
                        {#each timezones as timezone (timezone)}
                            <option value={timezone}>{timezone.replaceAll('_', ' ')}</option>
                        {/each}
                    </select>
                    <p class="form-hint">Used when displaying dates across the dashboard.</p>
                    <InputError message={form.errors.timezone} />
                </div>
            </div>
        {/snippet}
        {@render section('General', 'How the application identifies itself.', general)}

        {#snippet branding()}
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div
                    class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50"
                >
                    {#if shownLogo}
                        <img src={shownLogo} alt="Logo preview" class="size-full object-contain p-2" />
                    {:else}
                        <Image class="size-8 text-slate-300 dark:text-slate-600" />
                    {/if}
                </div>
                <div>
                    <div class="flex flex-wrap gap-2">
                        <label for="logo" class="btn-secondary cursor-pointer {editable ? '' : 'pointer-events-none opacity-50'}">
                            <Upload />
                            {shownLogo ? 'Replace logo' : 'Upload logo'}
                        </label>
                        {#if shownLogo}
                            <button type="button" class="btn-ghost text-red-600 dark:text-red-400" onclick={removeLogo}><Trash2 /> Remove</button>
                        {/if}
                    </div>
                    <input
                        id="logo"
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        class="sr-only"
                        bind:this={fileInput}
                        onchange={pickLogo}
                    />
                    <p class="form-hint">PNG, JPG or WebP, up to 1 MB. Square images look best.</p>
                    {#if form.remove_logo}
                        <p class="mt-1.5 text-xs text-amber-600 dark:text-amber-400">The current logo will be removed when you save.</p>
                    {/if}
                    {#if form.progress}
                        <div class="mt-3 h-1.5 w-48 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full bg-brand-500 transition-all" style="width: {form.progress.percentage}%"></div>
                        </div>
                    {/if}
                    <InputError message={form.errors.logo} />
                </div>
            </div>
        {/snippet}
        {@render section('Branding', 'Your logo appears in the sidebar and on the sign-in page.', branding)}
    </fieldset>

    {#if editable}
        <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
            {#if form.isDirty}
                <span class="mr-auto text-xs text-slate-500 dark:text-slate-400">You have unsaved changes.</span>
            {/if}
            <button type="submit" class="btn-primary" disabled={form.processing}>
                {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
                Save settings
            </button>
        </div>
    {/if}
</form>

{#if can('appearance.update')}
    <DefaultAppearanceForm {appearance} {allowUserThemes} />
{/if}
