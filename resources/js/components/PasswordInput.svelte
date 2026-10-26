<script>
    import Eye from '@lucide/svelte/icons/eye';
    import EyeOff from '@lucide/svelte/icons/eye-off';

    let { value = $bindable(''), id, invalid = false, class: className = '', ...rest } = $props();

    let visible = $state(false);
</script>

<div class="relative">
    <input
        {id}
        type={visible ? 'text' : 'password'}
        class="form-input pr-10 {className}"
        aria-invalid={invalid || undefined}
        bind:value
        {...rest}
    />
    <button
        type="button"
        class="absolute right-0 bottom-0 flex h-[38px] items-center px-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
        aria-label={visible ? 'Hide password' : 'Show password'}
        onclick={() => (visible = !visible)}
    >
        {#if visible}<EyeOff class="size-4" />{:else}<Eye class="size-4" />{/if}
    </button>
</div>
