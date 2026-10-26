<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded border border-line bg-transparent px-5 py-3 text-sm font-semibold text-ink-950 transition-colors duration-150 hover:bg-sand disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
