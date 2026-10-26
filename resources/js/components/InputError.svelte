<script>
    let { message = null } = $props();
</script>

{#if message}
    <p class="mt-1 text-sm text-red-600">{message}</p>
{/if}
