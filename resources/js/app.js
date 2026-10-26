import '../css/app.css';
import { createInertiaApp } from '@inertiajs/svelte';
import { mount } from 'svelte';

createInertiaApp({
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
        color: '#4f46e5',
    },
});
