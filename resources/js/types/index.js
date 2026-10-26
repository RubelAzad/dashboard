/**
 * Shared Inertia props provided by App\Http\Middleware\HandleInertiaRequests.
 *
 * @typedef {Object} AuthUser
 * @property {number} id
 * @property {string} name
 * @property {string} email
 * @property {string[]} roles
 * @property {string[]} permissions
 *
 * @typedef {Object} SharedProps
 * @property {{ name: string, logo: string|null }} app
 * @property {{ user: AuthUser|null }} auth
 * @property {{ success: string|null, error: string|null }} flash
 * @property {Record<string, string>} errors
 */

export {};
