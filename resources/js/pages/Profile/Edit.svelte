<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Profile' };
</script>

<script>
    import { useForm } from '@inertiajs/svelte';
    import Check from '@lucide/svelte/icons/check';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Avatar from '../../components/Avatar.svelte';
    import Badge from '../../components/Badge.svelte';
    import InputError from '../../components/InputError.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import PasswordInput from '../../components/PasswordInput.svelte';
    import ThemeToggle from '../../components/ThemeToggle.svelte';

    let { profile } = $props();

    const details = useForm(() => ({ name: profile.name, email: profile.email }));

    const password = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function saveDetails(event) {
        event.preventDefault();
        details.patch('/profile', { preserveScroll: true });
    }

    function savePassword(event) {
        event.preventDefault();
        password.put('/profile/password', {
            preserveScroll: true,
            errorBag: 'password',
            onSuccess: () => password.reset(),
            onError: () => password.reset('current_password'),
        });
    }
</script>

{#snippet saveButton(form, label)}
    <div class="flex items-center justify-end gap-3 rounded-b-xl border-t border-slate-200/80 bg-slate-50/60 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/40">
        {#if form.recentlySuccessful}
            <span class="inline-flex items-center gap-1 text-sm text-emerald-600 dark:text-emerald-400"><Check class="size-4" /> Saved</span>
        {/if}
        <button type="submit" class="btn-primary" disabled={form.processing}>
            {#if form.processing}<LoaderCircle class="animate-spin" />{/if}
            {label}
        </button>
    </div>
{/snippet}

<PageHeader title="Profile settings" description="Manage your personal details, password and preferences." />

<div class="grid gap-6 lg:grid-cols-3">
    <section class="card h-fit p-6 text-center">
        <Avatar name={details.name || profile.name} size="xl" class="mx-auto" />
        <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{profile.name}</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{profile.email}</p>
        <div class="mt-3 flex flex-wrap justify-center gap-1.5">
            {#each profile.roles as role (role)}
                <Badge tone={role === 'Super Admin' ? 'brand' : 'slate'}>{role}</Badge>
            {:else}
                <Badge>No role</Badge>
            {/each}
        </div>
        <p class="mt-4 text-xs text-slate-400">Member since {profile.created_at}</p>
        <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 text-left dark:border-slate-800">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Appearance</span>
            <ThemeToggle />
        </div>
    </section>

    <div class="space-y-6 lg:col-span-2">
        <form onsubmit={saveDetails} class="card" novalidate>
            <div class="card-header">
                <div>
                    <h2 class="card-title">Personal details</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Update your name and sign-in email.</p>
                </div>
            </div>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" type="text" class="form-input" autocomplete="name" aria-invalid={!!details.errors.name || undefined} bind:value={details.name} required />
                    <InputError message={details.errors.name} />
                </div>
                <div>
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" class="form-input" autocomplete="email" aria-invalid={!!details.errors.email || undefined} bind:value={details.email} required />
                    <InputError message={details.errors.email} />
                </div>
            </div>
            {@render saveButton(details, 'Save details')}
        </form>

        <form onsubmit={savePassword} class="card" novalidate>
            <div class="card-header">
                <div>
                    <h2 class="card-title">Change password</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Use a long, unique password to keep your account secure.</p>
                </div>
            </div>
            <div class="space-y-5 p-6">
                <div class="sm:max-w-sm">
                    <label for="current_password" class="form-label">Current password</label>
                    <PasswordInput id="current_password" autocomplete="current-password" invalid={!!password.errors.current_password} bind:value={password.current_password} />
                    <InputError message={password.errors.current_password} />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="new_password" class="form-label">New password</label>
                        <PasswordInput id="new_password" autocomplete="new-password" invalid={!!password.errors.password} bind:value={password.password} />
                        <InputError message={password.errors.password} />
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="form-label">Confirm new password</label>
                        <PasswordInput id="new_password_confirmation" autocomplete="new-password" bind:value={password.password_confirmation} />
                    </div>
                </div>
            </div>
            {@render saveButton(password, 'Update password')}
        </form>
    </div>
</div>
