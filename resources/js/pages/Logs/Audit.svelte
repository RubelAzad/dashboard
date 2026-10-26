<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Audit Log' };
</script>

<script>
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import FileClock from '@lucide/svelte/icons/file-clock';
    import Globe from '@lucide/svelte/icons/globe';
    import Monitor from '@lucide/svelte/icons/monitor';
    import { slide } from 'svelte/transition';
    import Avatar from '../../components/Avatar.svelte';
    import Badge from '../../components/Badge.svelte';
    import EmptyState from '../../components/EmptyState.svelte';
    import LogFilters from '../../components/LogFilters.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import Pagination from '../../components/Pagination.svelte';
    import { describeAgent, eventMeta, fieldLabel, timeAgo, toneChip } from '../../lib/logs';

    let { logs, filters, events, subjects } = $props();

    let expanded = $state(new Set());

    const subjectFilter = $derived({
        key: 'subject',
        label: 'Records',
        options: subjects.map((subject) => ({ value: subject, label: fieldLabel(subject) })),
    });

    function toggle(id) {
        const next = new Set(expanded);
        next.has(id) ? next.delete(id) : next.add(id);
        expanded = next;
    }

    /** Every attribute key that appears before or after the change. */
    function fields(log) {
        const { old = {}, new: next = {} } = log.properties ?? {};
        return [...new Set([...Object.keys(old ?? {}), ...Object.keys(next ?? {})])];
    }

    function format(value) {
        if (value === null || value === undefined || value === '') return '—';
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        return String(value);
    }
</script>

{#snippet valueCell(value, other, tone)}
    {#if Array.isArray(value)}
        <!-- Lists (roles, permissions) highlight the items that were added or removed. -->
        <div class="flex flex-wrap gap-1">
            {#each value as item (item)}
                {@const changed = !Array.isArray(other) || !other.includes(item)}
                <span
                    class="rounded px-1.5 py-0.5 font-mono text-[11px] {changed
                        ? tone === 'new'
                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                            : 'bg-red-100 text-red-800 line-through dark:bg-red-500/15 dark:text-red-300'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'}"
                >
                    {item}
                </span>
            {:else}
                <span class="text-slate-400">—</span>
            {/each}
        </div>
    {:else}
        <span
            class="break-all {value === undefined
                ? 'text-slate-400'
                : tone === 'new'
                  ? 'text-emerald-700 dark:text-emerald-400'
                  : 'text-red-700 line-through decoration-red-300 dark:text-red-400'}"
        >
            {format(value)}
        </span>
    {/if}
{/snippet}

<PageHeader title="Audit Log" description="Every change made to users, roles, permissions and settings, with before and after values." />

<div class="card overflow-hidden">
    <LogFilters url="/logs/audit" {filters} {events} extra={subjectFilter} />

    {#if logs.data.length === 0}
        <EmptyState icon={FileClock} title="No changes recorded" description="Changes made in the dashboard will appear here." />
    {:else}
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            {#each logs.data as log (log.id)}
                {@const meta = eventMeta(log.event)}
                {@const open = expanded.has(log.id)}
                {@const keys = fields(log)}
                <li>
                    <button
                        type="button"
                        class="flex w-full items-center gap-4 px-5 py-4 text-left transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/30"
                        aria-expanded={open}
                        onclick={() => toggle(log.id)}
                    >
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full {toneChip[meta.tone]}">
                            <meta.icon class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-sm font-medium text-slate-900 dark:text-white">{log.description}</span>
                                {#if log.subject_type}<Badge>{fieldLabel(log.subject_type)}</Badge>{/if}
                            </span>
                            <span class="mt-1 flex flex-wrap items-center gap-x-3 text-xs text-slate-500 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1.5">
                                    {#if log.causer}
                                        <Avatar name={log.causer.name} size="sm" class="size-5! text-[9px]!" />
                                        {log.causer.name}
                                    {:else}
                                        <span class="italic">System</span>
                                    {/if}
                                </span>
                                <span>{keys.length} {keys.length === 1 ? 'field' : 'fields'}</span>
                            </span>
                        </span>
                        <time datetime={log.created_at_iso} title={log.created_at} class="hidden shrink-0 text-right text-xs whitespace-nowrap text-slate-500 sm:block dark:text-slate-400">
                            <span class="block font-medium text-slate-700 dark:text-slate-300">{timeAgo(log.created_at_iso)}</span>
                            {log.created_at}
                        </time>
                        <ChevronDown class="size-4 shrink-0 text-slate-400 transition-transform {open ? 'rotate-180' : ''}" />
                    </button>

                    {#if open}
                        <div transition:slide={{ duration: 180 }} class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 sm:pl-17 dark:border-slate-800 dark:bg-slate-900/40">
                            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                                <table class="min-w-full text-sm">
                                    <thead class="border-b border-slate-200 text-left text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                                        <tr>
                                            <th class="w-36 px-4 py-2 font-medium">Field</th>
                                            <th class="px-4 py-2 font-medium">Before</th>
                                            <th class="px-4 py-2 font-medium">After</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        {#each keys as key (key)}
                                            {@const before = log.properties?.old?.[key]}
                                            {@const after = log.properties?.new?.[key]}
                                            <tr class="align-top">
                                                <td class="px-4 py-2.5 font-medium text-slate-700 dark:text-slate-300">{fieldLabel(key)}</td>
                                                <td class="px-4 py-2.5">{@render valueCell(before, after, 'old')}</td>
                                                <td class="px-4 py-2.5">{@render valueCell(after, before, 'new')}</td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                {#if log.ip_address}<span class="inline-flex items-center gap-1"><Globe class="size-3.5" /> {log.ip_address}</span>{/if}
                                {#if describeAgent(log.user_agent)}
                                    <span class="inline-flex items-center gap-1" title={log.user_agent}><Monitor class="size-3.5" /> {describeAgent(log.user_agent)}</span>
                                {/if}
                                <span class="sm:hidden">{log.created_at}</span>
                            </div>
                        </div>
                    {/if}
                </li>
            {/each}
        </ul>
    {/if}
    <Pagination paginator={logs} />
</div>
