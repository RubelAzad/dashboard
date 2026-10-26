<script>
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import RoleForm from '../../components/RoleForm.svelte';

    let { role, permissionGroups } = $props();
</script>

<AdminLayout title="Edit role: {role.name}">
    <RoleForm {role} {permissionGroups} action="/roles/{role.id}" method="put" submitLabel="Save changes" />
</AdminLayout>
