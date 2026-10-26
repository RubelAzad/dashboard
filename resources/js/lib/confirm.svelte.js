/**
 * Promise-based replacement for window.confirm(), rendered by components/ConfirmDialog.svelte.
 *
 * @typedef {Object} ConfirmOptions
 * @property {string} title
 * @property {string} [message]
 * @property {string} [confirmLabel]
 * @property {boolean} [danger]
 */

export const confirmState = $state({ open: false, options: /** @type {ConfirmOptions} */ ({ title: '' }), resolve: null });

/**
 * @param {ConfirmOptions} options
 * @returns {Promise<boolean>}
 */
export function confirm(options) {
    confirmState.resolve?.(false);

    return new Promise((resolve) => {
        confirmState.options = { confirmLabel: 'Confirm', danger: true, ...options };
        confirmState.resolve = resolve;
        confirmState.open = true;
    });
}

/** @param {boolean} result */
export function settleConfirm(result) {
    confirmState.resolve?.(result);
    confirmState.resolve = null;
    confirmState.open = false;
}
