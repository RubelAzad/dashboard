<script>
    import { Link, page, router } from '@inertiajs/svelte';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import LogOut from '@lucide/svelte/icons/log-out';
    import Palette from '@lucide/svelte/icons/palette';
    import UserCog from '@lucide/svelte/icons/user-cog';
    import { fly } from 'svelte/transition';
    import Avatar from './Avatar.svelte';
    import ThemeToggle from './ThemeToggle.svelte';

    /** `onappearance` opens the appearance panel owned by the layout. */
    let { onappearance } = $props();

    const user = $derived(page.props.auth.user);

    let open = $state(false);
    let root;

    function onWindowClick(event) {
        if (open && !root.contains(event.target)) open = false;
    }

    let signingOut = $state(false);

    // One click, no confirmation: signing out is harmless and easy to undo by signing in again.
    function logout() {
        signingOut = true;
        router.post('/logout', {}, { onFinish: () => (signingOut = false) });
    }
</script>

<svelte:window onclick={onWindowClick} onkeydown={(event) => event.key === 'Escape' && (open = false)} />

<div class="relative" bind:this={root}>
    <button
        type="button"
        class="flex items-center gap-2.5 rounded-full py-1 pr-2 pl-1 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800"
        aria-haspopup="menu"
        aria-expanded={open}
        onclick={() => (open = !open)}
    >
        <Avatar name={user.name} size="sm" />
        <span class="hidden text-left sm:block">
            <span class="block max-w-36 truncate text-sm leading-tight font-medium text-slate-900 dark:text-white">{user.name}</span>
            <span class="block max-w-36 truncate text-xs leading-tight text-slate-500 dark:text-slate-400">
                {user.roles[0] ?? 'No role'}
            </span>
        </span>
        <ChevronDown class="hidden size-4 text-slate-400 transition-transform sm:block {open ? 'rotate-180' : ''}" />
    </button>

    {#if open}
        <div
            transition:fly={{ y: -6, duration: 150 }}
            class="absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900"
            role="menu"
        >
            <div class="flex items-center gap-3 px-2.5 py-2.5">
                <Avatar name={user.name} />
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{user.name}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{user.email}</p>
                </div>
            </div>
            <div class="my-1 h-px bg-slate-100 dark:bg-slate-800"></div>
            <Link
                prefetch
                href="/profile"
                role="menuitem"
                class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                onclick={() => (open = false)}
            >
                <UserCog class="size-4 text-slate-400" />
                Profile settings
            </Link>
            <button
                type="button"
                role="menuitem"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                onclick={() => {
                    open = false;
                    onappearance();
                }}
            >
                <Palette class="size-4 text-slate-400" />
                Appearance
            </button>
            <div class="flex items-center justify-between px-2.5 py-1.5">
                <span class="text-sm text-slate-700 dark:text-slate-300">Mode</span>
                <ThemeToggle />
            </div>
            <div class="my-1 h-px bg-slate-100 dark:bg-slate-800"></div>
            <button
                type="button"
                role="menuitem"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-red-600 hover:bg-red-50 disabled:opacity-60 dark:text-red-400 dark:hover:bg-red-500/10"
                disabled={signingOut}
                onclick={logout}
            >
                {#if signingOut}
                    <LoaderCircle class="size-4 animate-spin" />
                    Signing out…
                {:else}
                    <LogOut class="size-4" />
                    Sign out
                {/if}
            </button>
        </div>
    {/if}
</div>
