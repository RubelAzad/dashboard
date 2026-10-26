<script>
    let { name = '', size = 'md', class: className = '' } = $props();

    const palettes = [
        'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
        'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
    ];

    const sizes = { sm: 'size-7 text-[11px]', md: 'size-9 text-xs', lg: 'size-14 text-lg', xl: 'size-20 text-2xl' };

    const initials = $derived(
        name
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0].toUpperCase())
            .join('') || '?',
    );

    // The same name always gets the same colour.
    const palette = $derived(palettes[[...name].reduce((sum, char) => sum + char.charCodeAt(0), 0) % palettes.length]);
</script>

<span
    class="inline-flex shrink-0 items-center justify-center rounded-full font-semibold select-none {sizes[size]} {palette} {className}"
    aria-hidden="true"
>
    {initials}
</span>
