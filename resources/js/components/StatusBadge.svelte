<script>
    import Badge from './Badge.svelte';

    let { active } = $props();
</script>

<Badge tone={active ? 'green' : 'slate'} dot>{active ? 'Active' : 'Inactive'}</Badge>
