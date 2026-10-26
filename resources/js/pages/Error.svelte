<script>
    import { Link, page, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Ban from '@lucide/svelte/icons/ban';
    import Hourglass from '@lucide/svelte/icons/hourglass';
    import House from '@lucide/svelte/icons/house';
    import LogOut from '@lucide/svelte/icons/log-out';
    import SearchX from '@lucide/svelte/icons/search-x';
    import ServerCrash from '@lucide/svelte/icons/server-crash';
    import { can } from '../lib/permissions';

    let { status } = $props();

    const content = {
        403: { icon: Ban, title: 'Access denied', description: "You don't have permission to view this page." },
        404: { icon: SearchX, title: 'Page not found', description: "The page you're looking for doesn't exist or has moved." },
        500: { icon: ServerCrash, title: 'Something went wrong', description: 'An unexpected error occurred on our side. Please try again.' },
        503: { icon: Hourglass, title: 'Under maintenance', description: "We're doing some work right now. Please check back soon." },
    };

    const info = $derived(content[status] ?? content[500]);
    // Shared props are missing when the error happens before the Inertia middleware runs.
    const appName = $derived(page.props.app?.name ?? 'Dashboard');
    const signedIn = $derived(!!page.props.auth?.user);
    // Users without dashboard access would bounce straight back here, so send them to their profile.
    const home = $derived(
        !signedIn ? { href: '/login', label: 'Go to sign in' }
        : can('dashboard.view') ? { href: '/dashboard', label: 'Back to dashboard' }
        : { href: '/profile', label: 'Go to your profile' },
    );
</script>

<svelte:head>
    <title>{info.title} · {appName}</title>
</svelte:head>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute top-1/2 left-1/2 size-[36rem] -translate-1/2 rounded-full bg-brand-500/10 blur-3xl"></div>
    </div>

    <div class="relative max-w-md animate-slide-up text-center">
        <div
            class="mx-auto mb-6 flex size-16 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-lg ring-1 shadow-brand-900/5 ring-slate-200 dark:bg-slate-900 dark:text-brand-400 dark:ring-slate-800"
        >
            <info.icon class="size-7" />
        </div>
        <p class="text-sm font-semibold tracking-widest text-brand-600 dark:text-brand-400">ERROR {status}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl dark:text-white">{info.title}</h1>
        <p class="mt-3 text-slate-500 dark:text-slate-400">{info.description}</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <button type="button" class="btn-secondary" onclick={() => history.back()}>
                <ArrowLeft />
                Go back
            </button>
            <Link prefetch href={home.href} class="btn-primary">
                <House />
                {home.label}
            </Link>
        </div>
        {#if signedIn}
            <button
                type="button"
                class="mt-6 inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                onclick={() => router.post('/logout')}
            >
                <LogOut class="size-4" />
                Sign in with a different account
            </button>
        {/if}
    </div>
</div>
