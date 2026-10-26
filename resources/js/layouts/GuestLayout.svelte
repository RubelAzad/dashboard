<script>
    import { page } from '@inertiajs/svelte';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import LayoutDashboard from '@lucide/svelte/icons/layout-dashboard';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import FlashMessage from '../components/FlashMessage.svelte';
    import ThemeToggle from '../components/ThemeToggle.svelte';

    let { title = '', children } = $props();

    const app = $derived(page.props.app);

    const highlights = [
        { icon: LayoutDashboard, text: 'A live overview of your users, roles and activity.' },
        { icon: ShieldCheck, text: 'Fine-grained roles and permissions, enforced on the server.' },
        { icon: KeyRound, text: 'Secure sessions with rate-limited sign-in.' },
    ];
</script>

<svelte:head>
    <title>{title ? `${title} · ${app.name}` : app.name}</title>
</svelte:head>

<div class="grid min-h-screen lg:grid-cols-2">
    <!-- Brand panel -->
    <div class="relative hidden overflow-hidden bg-slate-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-40 -left-32 size-128 rounded-full bg-brand-600/30 blur-3xl"></div>
            <div class="absolute -right-24 -bottom-48 size-112 rounded-full bg-sky-500/20 blur-3xl"></div>
            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,rgb(255_255_255/0.04)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.04)_1px,transparent_1px)] bg-size-[48px_48px] mask-[radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"
            ></div>
        </div>

        <div class="relative flex items-center gap-3">
            {#if app.logo}
                <img src={app.logo} alt="" class="size-10 rounded-xl bg-white object-contain p-1" />
            {:else}
                <span class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-brand-400 to-brand-700 font-bold text-white shadow-lg shadow-brand-500/30">
                    {app.name.charAt(0).toUpperCase()}
                </span>
            {/if}
            <span class="text-lg font-semibold text-white">{app.name}</span>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-4xl leading-tight font-semibold tracking-tight text-white">Everything you need to run your team.</h2>
            <ul class="mt-8 space-y-4">
                {#each highlights as item (item.text)}
                    <li class="flex items-start gap-3 text-slate-300">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-brand-300 ring-1 ring-white/10">
                            <item.icon class="size-4" />
                        </span>
                        <span class="pt-1 text-sm">{item.text}</span>
                    </li>
                {/each}
            </ul>
        </div>

        <p class="relative text-xs text-slate-500">&copy; {new Date().getFullYear()} {app.name}</p>
    </div>

    <!-- Form panel -->
    <div class="relative flex flex-col px-6 py-10 sm:px-10">
        <div class="flex justify-end">
            <ThemeToggle />
        </div>
        <div class="flex flex-1 items-center justify-center">
            <div class="w-full max-w-sm animate-slide-up">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    {#if app.logo}
                        <img src={app.logo} alt="" class="size-10 rounded-xl object-contain" />
                    {:else}
                        <span class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-brand-400 to-brand-700 font-bold text-white">
                            {app.name.charAt(0).toUpperCase()}
                        </span>
                    {/if}
                    <span class="text-lg font-semibold">{app.name}</span>
                </div>
                {@render children()}
            </div>
        </div>
    </div>
</div>

<FlashMessage />
