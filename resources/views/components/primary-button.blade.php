<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded bg-gold-500 px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-gold-700 hover:text-white disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
