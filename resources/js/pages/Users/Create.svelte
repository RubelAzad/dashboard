<script>
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import UserForm from '../../components/UserForm.svelte';

    let { roles } = $props();
</script>

<AdminLayout title="New user">
    <UserForm {roles} action="/users" submitLabel="Create user" />
</AdminLayout>
