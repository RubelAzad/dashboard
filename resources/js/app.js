import '../css/app.css';
import { createInertiaApp, router } from '@inertiajs/svelte';
import { mount } from 'svelte';
import AdminLayout from './layouts/AdminLayout.svelte';
import GuestLayout from './layouts/GuestLayout.svelte';
import { applyAppearance, effectiveAppearance } from './lib/appearance';

createInertiaApp({
    // Persistent layouts: the sidebar/header stay mounted across visits and only the page swaps.
    // Pages pass layout props (e.g. the title) with `export const layout = { title }`.
    layout: (name) => (name.startsWith('Auth/') ? GuestLayout : name === 'Error' ? null : AdminLayout),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.svelte', { eager: true });
        const page = pages[`./pages/${name}.svelte`];

        if (!page) {
            throw new Error(`Inertia page not found: ${name}`);
        }

        return page;
    },
    setup({ el, App, props }) {
        mount(App, { target: el, props });
    },
    progress: {
        color: 'var(--color-brand-500)',
    },
});

// Keep <html> in sync with the theme after every visit (sign-in, saving a theme, admin changes).
router.on('navigate', (event) => applyAppearance(effectiveAppearance(event.detail.page.props.theme)));

// Links prefetch on hover so clicks render instantly. Saving or deleting anything can make
// those cached pages stale, so drop them before every non-GET request.
router.on('before', (event) => {
    if (event.detail.visit.method !== 'get') router.flushAll();
});

// Back/Forward restores Inertia pages from browser history without asking the server, so a
// signed-out user could still see the dashboard. Re-check the session on every history step:
// the auth middleware then redirects to /login (and the guest middleware away from it).
// The check runs once Inertia has finished restoring the page; starting it inside the popstate
// handler gets it cancelled by that restore.
let restoringHistory = false;
window.addEventListener('popstate', () => (restoringHistory = true));
router.on('navigate', () => {
    if (!restoringHistory) return;
    restoringHistory = false;
    router.reload({ only: ['auth'] });
});

// Same for pages restored whole from the browser's back/forward cache.
window.addEventListener('pageshow', (event) => {
    if (event.persisted) window.location.reload();
});
