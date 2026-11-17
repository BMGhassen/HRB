{{-- Dark / light mode toggle. Optional: pass 'class' to override styling. --}}
<button type="button" onclick="toggleTheme()" title="Mode clair / sombre" aria-label="Basculer entre mode clair et sombre"
        class="{{ $class ?? 'flex items-center justify-center w-7 h-7 rounded-full border border-zinc-700 text-zinc-300 hover:text-lion-accent hover:border-[#FFC107] transition-colors' }}">
    <i class="fa-solid fa-sun theme-sun"></i>
    <i class="fa-solid fa-moon theme-moon"></i>
</button>
