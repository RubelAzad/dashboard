<script module>
    // Rendered inside the persistent layout chosen in app.js; only this page re-renders on navigation.
    export const layout = { title: 'Activity Log' };
</script>

<script>
    import Activity from '@lucide/svelte/icons/activity';
    import Globe from '@lucide/svelte/icons/globe';
    import Monitor from '@lucide/svelte/icons/monitor';
    import Avatar from '../../components/Avatar.svelte';
    import Badge from '../../components/Badge.svelte';
    import EmptyState from '../../components/EmptyState.svelte';
    import LogFilters from '../../components/LogFilters.svelte';
    import PageHeader from '../../components/PageHeader.svelte';
    import Pagination from '../../components/Pagination.svelte';
    import { describeAgent, eventMeta, timeAgo, toneChip } from '../../lib/logs';

    let { logs, filters, events } = $props();

    const types = {
        key: 'type',
        label: 'Types',
        options: [
            { value: 'activity', label: 'Account' },
            { value: 'audit', label: 'Data changes' },
        ],
    };
</script>

<PageHeader title="Activity Log" description="A timeline of everything that happened: sign-ins, sign-outs, failed attempts and data changes." />

<div class="card overflow-hidden">
    <LogFilters url="/logs/activity" {filters} {events} extra={types} />

    {#if logs.data.length === 0}
        <EmptyState icon={Activity} title="No activity found" description="Try widening the date range or clearing the filters." />
    {:else}
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            {#each logs.data as log (log.id)}
                {@const meta = eventMeta(log.event)}
                <li class="flex gap-4 px-5 py-4 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/30">
                    <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full {toneChip[meta.tone]}">
                        <meta.icon class="size-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{log.description}</p>
                            <Badge tone={meta.tone}>{meta.label}</Badge>
                            {#if log.log_name === 'audit'}
                                <Badge>Data change</Badge>
                            {/if}
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <span class="inline-flex items-center gap-1.5">
                                {#if log.causer}
                                    <Avatar name={log.causer.name} size="sm" class="size-5! text-[9px]!" />
                                    {log.causer.name}
                                {:else}
                                    <span class="italic">Guest / system</span>
                                {/if}
                            </span>
                            {#if log.ip_address}
                                <span class="inline-flex items-center gap-1"><Globe class="size-3.5" /> {log.ip_address}</span>
                            {/if}
                            {#if describeAgent(log.user_agent)}
                                <span class="inline-flex items-center gap-1" title={log.user_agent}><Monitor class="size-3.5" /> {describeAgent(log.user_agent)}</span>
                            {/if}
                        </div>
                    </div>
                    <time datetime={log.created_at_iso} title={log.created_at} class="shrink-0 text-right text-xs whitespace-nowrap text-slate-500 dark:text-slate-400">
                        <span class="block font-medium text-slate-700 dark:text-slate-300">{timeAgo(log.created_at_iso)}</span>
                        <span class="hidden sm:block">{log.created_at}</span>
                    </time>
                </li>
            {/each}
        </ul>
    {/if}
    <Pagination paginator={logs} />
</div>
