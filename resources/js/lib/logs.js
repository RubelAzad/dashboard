import CircleX from '@lucide/svelte/icons/circle-x';
import KeyRound from '@lucide/svelte/icons/key-round';
import LogIn from '@lucide/svelte/icons/log-in';
import LogOut from '@lucide/svelte/icons/log-out';
import Pencil from '@lucide/svelte/icons/pencil';
import Plus from '@lucide/svelte/icons/plus';
import ShieldAlert from '@lucide/svelte/icons/shield-alert';
import Trash2 from '@lucide/svelte/icons/trash-2';

/** Display metadata for each logged event. Unknown events fall back to a neutral style. */
const EVENTS = {
    login: { label: 'Signed in', icon: LogIn, tone: 'green' },
    logout: { label: 'Signed out', icon: LogOut, tone: 'slate' },
    login_failed: { label: 'Failed sign-in', icon: CircleX, tone: 'red' },
    lockout: { label: 'Locked out', icon: ShieldAlert, tone: 'red' },
    password_changed: { label: 'Password changed', icon: KeyRound, tone: 'amber' },
    created: { label: 'Created', icon: Plus, tone: 'green' },
    updated: { label: 'Updated', icon: Pencil, tone: 'brand' },
    deleted: { label: 'Deleted', icon: Trash2, tone: 'red' },
};

export function eventMeta(event) {
    return EVENTS[event] ?? { label: event.replaceAll('_', ' '), icon: Pencil, tone: 'slate' };
}

/** Icon-chip colours matching Badge tones. */
export const toneChip = {
    slate: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
    green: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    red: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
};

const relative = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
const UNITS = [
    ['year', 31536000],
    ['month', 2592000],
    ['week', 604800],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
];

/** "5 minutes ago" style text for an ISO timestamp. */
export function timeAgo(iso) {
    if (!iso) return '';
    const seconds = Math.round((new Date(iso).getTime() - Date.now()) / 1000);
    for (const [unit, size] of UNITS) {
        if (Math.abs(seconds) >= size) return relative.format(Math.round(seconds / size), unit);
    }
    return 'just now';
}

/** Rough "Chrome on Windows" summary of a user-agent string. */
export function describeAgent(agent) {
    if (!agent) return null;
    const browser = /Edg\//.test(agent)
        ? 'Edge'
        : /OPR\//.test(agent)
          ? 'Opera'
          : /Firefox\//.test(agent)
            ? 'Firefox'
            : /Chrome\//.test(agent)
              ? 'Chrome'
              : /Safari\//.test(agent)
                ? 'Safari'
                : null;
    const os = /Windows/.test(agent)
        ? 'Windows'
        : /Android/.test(agent)
          ? 'Android'
          : /iPhone|iPad/.test(agent)
            ? 'iOS'
            : /Mac OS X/.test(agent)
              ? 'macOS'
              : /Linux/.test(agent)
                ? 'Linux'
                : null;
    if (!browser && !os) return agent.length > 40 ? `${agent.slice(0, 40)}…` : agent;
    return [browser, os].filter(Boolean).join(' on ');
}

/** Human label for an audited attribute key, e.g. "is_active" -> "Is active". */
export function fieldLabel(key) {
    const labels = { is_active: 'Active', app_name: 'Application name', logo_path: 'Logo' };
    return labels[key] ?? key.charAt(0).toUpperCase() + key.slice(1).replaceAll('_', ' ');
}
