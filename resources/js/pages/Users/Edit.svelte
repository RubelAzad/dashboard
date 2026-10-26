<script>
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import UserForm from '../../components/UserForm.svelte';

    let { user, roles } = $props();
</script>

<AdminLayout title="Edit {user.name}">
    <UserForm {user} {roles} action="/users/{user.id}" method="put" submitLabel="Save changes" />
</AdminLayout>
