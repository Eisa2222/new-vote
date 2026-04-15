{{-- Shared brand identity head: fonts + Tailwind config + FPA palette --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
    // FPA-inspired palette: institutional dark green + warm gold accent + clean ink.
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['{{ app()->getLocale() === 'ar' ? 'Tajawal' : 'Inter' }}',
                           'Tajawal', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
                colors: {
                    // Institutional green — close in spirit to the official green of Saudi football
                    brand: {
                        50:  '#ECF5EF',
                        100: '#D0E6D6',
                        200: '#A3CEB0',
                        300: '#6FB185',
                        400: '#3F9261',
                        500: '#1F7A49',
                        600: '#115C42',  // primary
                        700: '#0B3D2E',  // dark primary (sidebar, hero)
                        800: '#083024',
                        900: '#052219',
                    },
                    // Warm gold accent for winners / CTAs emphasis
                    accent: {
                        400: '#DDB97A',
                        500: '#C8A365',
                        600: '#A8834A',
                    },
                    ink: {
                        50:  '#F8FAFC',
                        100: '#F1F5F9',
                        200: '#E2E8F0',
                        300: '#CBD5E1',
                        500: '#64748B',
                        700: '#334155',
                        800: '#1E293B',
                        900: '#0F172A',
                        950: '#020617',
                    },
                    danger:  { 500: '#D94552', 600: '#B5343F' },
                    warning: { 500: '#E8A951' },
                    success: { 500: '#16A34A' },
                    info:    { 500: '#2563EB' },
                },
                boxShadow: {
                    brand: '0 10px 30px -10px rgba(11, 61, 46, 0.25)',
                },
            },
        },
    };
</script>
<style>
    body { font-family: '{{ app()->getLocale() === 'ar' ? 'Tajawal' : 'Inter' }}', system-ui, sans-serif; }
    .btn-brand       { @apply bg-brand-600 hover:bg-brand-700 text-white rounded-xl px-5 py-2.5 font-semibold transition; }
    .btn-brand-lg    { @apply bg-brand-600 hover:bg-brand-700 text-white rounded-2xl px-8 py-3.5 font-semibold text-lg shadow-brand transition; }
    .btn-ghost       { @apply text-ink-700 border border-ink-200 hover:bg-ink-50 rounded-xl px-4 py-2 font-medium; }
    .btn-danger      { @apply text-danger-600 border border-danger-500/40 hover:bg-danger-500/10 rounded-xl px-4 py-2 font-medium; }
    .card            { @apply bg-white rounded-2xl border border-ink-200 shadow-sm p-6; }
    .badge           { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold; }
    .badge-active    { @apply bg-brand-100 text-brand-700; }
    .badge-published { @apply bg-info-500/10 text-info-500; }
    .badge-draft     { @apply bg-warning-500/10 text-warning-500; }
    .badge-closed    { @apply bg-ink-100 text-ink-700; }
    .badge-archived  { @apply bg-ink-100 text-ink-500; }
    .badge-inactive  { @apply bg-ink-100 text-ink-500; }
</style>
