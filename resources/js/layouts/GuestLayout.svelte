<script>
    import { page } from '@inertiajs/svelte';

    let { title = '', children } = $props();

    const app = $derived(page.props.app);
</script>

<svelte:head>
    <title>{title ? `${title} - ${app.name}` : app.name}</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center">
            {#if app.logo}
                <img src={app.logo} alt="" class="mx-auto mb-3 h-12 w-12 object-contain" />
            {/if}
            <h1 class="text-2xl font-semibold text-slate-900">{app.name}</h1>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            {@render children()}
        </div>
    </div>
</div>
