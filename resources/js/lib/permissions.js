import { page } from '@inertiajs/svelte';

const SUPER_ADMIN = 'Super Admin';

/**
 * UI-only permission check. The server must still authorize every request.
 *
 * @param {string} permission e.g. "users.create"
 */
export function can(permission) {
    const user = page.props.auth?.user;

    if (!user) return false;

    return user.roles.includes(SUPER_ADMIN) || user.permissions.includes(permission);
}
