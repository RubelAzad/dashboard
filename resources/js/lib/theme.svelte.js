const STORAGE_KEY = 'theme';
const media = typeof window !== 'undefined' ? window.matchMedia('(prefers-color-scheme: dark)') : null;

function stored() {
    try {
        return localStorage.getItem(STORAGE_KEY) ?? 'system';
    } catch {
        return 'system';
    }
}

/** Reactive theme preference: "light", "dark" or "system". */
export const theme = $state({ mode: typeof window !== 'undefined' ? stored() : 'system' });

function apply() {
    const dark = theme.mode === 'dark' || (theme.mode === 'system' && media?.matches);
    document.documentElement.classList.toggle('dark', dark);
}

/** @param {'light'|'dark'|'system'} mode */
export function setTheme(mode) {
    theme.mode = mode;
    try {
        localStorage.setItem(STORAGE_KEY, mode);
    } catch {
        // Storage can be blocked; the choice then lasts for this page only.
    }
    apply();
}

media?.addEventListener('change', () => theme.mode === 'system' && apply());
