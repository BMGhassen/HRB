{{--
    Shared theme setup (dark / light).
    Include in <head> right after the Tailwind CDN script.

    How it works: the colours used across the views (zinc-*, white, lion-*)
    are CSS variables. Adding the "light" class on <html> swaps the variables,
    so existing classes like bg-zinc-900 / text-white flip automatically.
    Classes that must NOT flip (e.g. product image boxes) use literal values
    like bg-[#fff] or bg-black.
--}}
<script>
    // Apply saved theme before first paint (avoids a dark flash in light mode)
    (function () {
        try {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light');
            }
        } catch (e) {}
    })();

    function toggleTheme() {
        const isLight = document.documentElement.classList.toggle('light');
        try { localStorage.setItem('theme', isLight ? 'light' : 'dark'); } catch (e) {}
    }

    (function () {
        const v = (name) => `rgb(var(--${name}) / <alpha-value>)`;
        const zinc = {};
        [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].forEach(s => zinc[s] = v('zinc-' + s));

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        zinc,
                        white: v('white'),
                        lion: {
                            light: '#FFD54F',
                            DEFAULT: '#FFC107',
                            dark: '#E5A100',
                            black: v('lion-black'),   // page background
                            card: v('lion-card'),     // cards, navbar
                            ink: v('lion-ink'),       // footer, top utility bar
                            accent: v('lion-accent'),             // brand yellow used as TEXT
                            'accent-light': v('lion-accent-light'),
                        }
                    }
                }
            }
        };
    })();
</script>
<style>
    /* Dark palette: default for the page, and always for .force-dark sections (e.g. the header) */
    :root,
    html.light .force-dark {
        color-scheme: dark;
        --white: 255 255 255;
        --zinc-50: 250 250 250;  --zinc-100: 244 244 245; --zinc-200: 228 228 231;
        --zinc-300: 212 212 216; --zinc-400: 161 161 170; --zinc-500: 113 113 122;
        --zinc-600: 82 82 91;    --zinc-700: 63 63 70;    --zinc-800: 39 39 42;
        --zinc-900: 24 24 27;    --zinc-950: 9 9 11;
        --lion-black: 10 13 23;
        --lion-card: 20 24 36;
        --lion-ink: 0 0 0;
        --lion-accent: 255 193 7;
        --lion-accent-light: 255 213 79;
    }

    html.light {
        color-scheme: light;
        --white: 24 24 27;                                  /* "white" text becomes near-black */
        --zinc-50: 9 9 11;       --zinc-100: 24 24 27;    --zinc-200: 39 39 42;
        --zinc-300: 63 63 70;    --zinc-400: 82 82 91;    --zinc-500: 113 113 122;
        --zinc-600: 161 161 170; --zinc-700: 212 212 216; --zinc-800: 228 228 231;
        --zinc-900: 244 244 245; --zinc-950: 250 250 250;
        --lion-black: 241 242 245;
        --lion-card: 255 255 255;
        --lion-ink: 255 255 255;
        --lion-accent: 180 83 9;                            /* amber-700: readable on white */
        --lion-accent-light: 146 64 14;
    }

    /* Toggle button icon: show the sun in dark mode, the moon in light mode */
    /* (html-prefixed so these beat Font Awesome's .fa-solid display rule regardless of load order) */
    html:not(.light) .theme-moon,
    html.light .theme-sun { display: none; }
</style>
