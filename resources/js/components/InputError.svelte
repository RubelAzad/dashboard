<script>
    import CircleAlert from '@lucide/svelte/icons/circle-alert';

    let { message = null } = $props();
</script>

{#if message}
    <p class="mt-1.5 flex items-start gap-1.5 text-sm text-red-600 dark:text-red-400">
        <CircleAlert class="mt-0.5 size-3.5 shrink-0" />
        {message}
    </p>
{/if}
