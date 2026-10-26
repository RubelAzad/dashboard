/**
 * Theme options shown in the pickers. Keys must match App\Support\Appearance::OPTIONS; the
 * colours, fonts and sizes they map to live in resources/css/app.css ([data-*] rules).
 */
export const OPTIONS = {
    design: [
        { value: 'midnight', label: 'Midnight', hint: 'Deep sidebar, light canvas' },
        { value: 'daylight', label: 'Daylight', hint: 'Airy, all-light workspace' },
        { value: 'floating', label: 'Floating', hint: 'Inset colourful sidebar' },
    ],
    accent: [
        { value: 'indigo', label: 'Indigo', swatch: 'oklch(0.55 0.22 277)' },
        { value: 'violet', label: 'Violet', swatch: 'oklch(0.55 0.22 296)' },
        { value: 'blue', label: 'Blue', swatch: 'oklch(0.55 0.21 257)' },
        { value: 'teal', label: 'Teal', swatch: 'oklch(0.55 0.13 192)' },
        { value: 'emerald', label: 'Emerald', swatch: 'oklch(0.55 0.16 158)' },
        { value: 'amber', label: 'Amber', swatch: 'oklch(0.6 0.17 58)' },
        { value: 'rose', label: 'Rose', swatch: 'oklch(0.56 0.21 12)' },
        { value: 'graphite', label: 'Graphite', swatch: 'oklch(0.4 0.03 260)' },
    ],
    neutral: [
        { value: 'slate', label: 'Slate', hint: 'Cool', swatch: 'oklch(0.55 0.046 257)' },
        { value: 'zinc', label: 'Zinc', hint: 'Neutral', swatch: 'oklch(0.55 0.006 286)' },
        { value: 'stone', label: 'Stone', hint: 'Warm', swatch: 'oklch(0.55 0.007 60)' },
    ],
    font: [
        { value: 'inter', label: 'Inter', family: "'Inter Variable'" },
        { value: 'jakarta', label: 'Plus Jakarta Sans', family: "'Plus Jakarta Sans Variable'" },
        { value: 'manrope', label: 'Manrope', family: "'Manrope Variable'" },
        { value: 'dm-sans', label: 'DM Sans', family: "'DM Sans Variable'" },
        { value: 'system', label: 'System', family: 'system-ui' },
    ],
    icons: [
        { value: 'light', label: 'Light', stroke: 1.25 },
        { value: 'regular', label: 'Regular', stroke: 1.75 },
        { value: 'bold', label: 'Bold', stroke: 2.25 },
    ],
    radius: [
        { value: 'sharp', label: 'Sharp', preview: '3px' },
        { value: 'soft', label: 'Soft', preview: '8px' },
        { value: 'round', label: 'Round', preview: '14px' },
    ],
    density: [
        { value: 'comfortable', label: 'Comfortable' },
        { value: 'compact', label: 'Compact' },
    ],
};

/** The theme a user should see, from the shared `theme` prop. */
export function effectiveAppearance(theme) {
    if (!theme) return null;
    return theme.allowCustom && theme.mine ? { ...theme.defaults, ...theme.mine } : theme.defaults;
}

/** Writes the theme to <html> data attributes; CSS does the rest. */
export function applyAppearance(appearance) {
    if (!appearance) return;
    const root = document.documentElement;
    for (const key of Object.keys(OPTIONS)) {
        if (appearance[key]) root.dataset[key] = appearance[key];
    }
}
