<script>
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import RoleForm from '../../components/RoleForm.svelte';

    let { permissionGroups } = $props();
</script>

<AdminLayout title="New role">
    <RoleForm {permissionGroups} action="/roles" submitLabel="Create role" />
</AdminLayout>
