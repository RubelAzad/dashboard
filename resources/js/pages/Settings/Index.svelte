<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import InputError from '../../components/InputError.svelte';
    import { can } from '../../lib/permissions';

    let { settings, timezones } = $props();

    // File uploads must be sent as multipart POST, so PUT is spoofed via _method.
    const form = useForm(() => ({
        _method: 'put',
        app_name: settings.app_name,
        timezone: settings.timezone,
        logo: null,
        remove_logo: false,
    }));

    let fileInput;
    const editable = can('settings.update');

    function submit(event) {
        event.preventDefault();
        form.post('/settings', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('logo', 'remove_logo');
                if (fileInput) fileInput.value = '';
            },
        });
    }
</script>

<AdminLayout title="Settings">
    <form onsubmit={submit} class="card max-w-2xl space-y-5 p-6" novalidate>
        <fieldset disabled={!editable} class="space-y-5">
            <div>
                <label for="app_name" class="form-label">Application name</label>
                <input id="app_name" type="text" class="form-input" bind:value={form.app_name} required />
                <InputError message={form.errors.app_name} />
            </div>

            <div>
                <label for="timezone" class="form-label">Timezone</label>
                <select id="timezone" class="form-input" bind:value={form.timezone}>
                    {#each timezones as timezone (timezone)}
                        <option value={timezone}>{timezone}</option>
                    {/each}
                </select>
                <p class="mt-1 text-xs text-slate-500">Used when displaying dates across the dashboard.</p>
                <InputError message={form.errors.timezone} />
            </div>

            <div>
                <label for="logo" class="form-label">Logo</label>
                {#if settings.logo_url && !form.remove_logo}
                    <img src={settings.logo_url} alt="Current logo" class="mt-2 h-14 w-14 rounded border border-slate-200 object-contain" />
                {/if}
                <input
                    id="logo"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-slate-200"
                    bind:this={fileInput}
                    onchange={(event) => (form.logo = event.currentTarget.files[0] ?? null)}
                />
                <p class="mt-1 text-xs text-slate-500">PNG, JPG or WebP, up to 1 MB.</p>
                {#if form.progress}
                    <progress value={form.progress.percentage} max="100" class="mt-2 w-full">{form.progress.percentage}%</progress>
                {/if}
                <InputError message={form.errors.logo} />
                {#if settings.logo_url}
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" class="form-checkbox" bind:checked={form.remove_logo} />
                        Remove current logo
                    </label>
                {/if}
            </div>
        </fieldset>

        {#if editable}
            <div class="flex justify-end border-t border-slate-200 pt-5">
                <button type="submit" class="btn-primary" disabled={form.processing}>Save settings</button>
            </div>
        {:else}
            <p class="text-sm text-slate-500">You can view these settings but not change them.</p>
        {/if}
    </form>
</AdminLayout>
